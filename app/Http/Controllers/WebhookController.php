<?php
namespace App\Http\Controllers;
use App\Models\{Booking,Payment,Showtime};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class WebhookController extends Controller
{
    // Generic educational webhook (not an integration with a named bank/provider).
    // HMAC SHA-256 of RAW JSON request body, header X-CINEMAX-SIGNATURE.
    public function receive(Request $request){
        $secret=config('cinemax.webhook_secret');
        if(!$secret || $secret==='change-me-before-using-webhooks')return response()->json(['error'=>'Webhook not configured'],503);
        $signature=(string)$request->header('X-CINEMAX-SIGNATURE','');
        if(!hash_equals(hash_hmac('sha256',$request->getContent(),$secret),$signature))return response()->json(['error'=>'Invalid signature'],403);
        $data=$request->validate(['booking_code'=>['required','string'],'transaction_code'=>['required','string','max:100'], 'amount'=>['required','numeric','min:0'],'status'=>['required','in:thanh_cong,that_bai']]);
        $b=Booking::where('booking_code',$data['booking_code'])->first();
        if(!$b)return response()->json(['error'=>'Booking not found'],404);
        try{
            $result=DB::transaction(function()use($b,$data){
                $showtime=Showtime::whereKey($b->showtime_id)->lockForUpdate()->firstOrFail();
                $booking=Booking::lockForUpdate()->findOrFail($b->id);
                if($showtime->status==='da_huy')return 'cancelled_showtime';
                if((int)round((float)$booking->total_amount*100)!==(int)round((float)$data['amount']*100))return 'amount_mismatch';
                $duplicate=Payment::where('transaction_code',$data['transaction_code'])->first();
                if($duplicate)return (int)$duplicate->booking_id===(int)$booking->id?'already_processed':'duplicate_code';
                if($booking->status!=='cho_thanh_toan')return 'already_processed';
                if($booking->expired){$booking->update(['status'=>'da_huy']);return 'expired';}
                $pay=Payment::where('booking_id',$booking->id)->where('status','cho_thanh_toan')->lockForUpdate()->latest('id')->first();
                if(!$pay)return 'no_pending_payment';
                $pay->update(['status'=>$data['status'],'transaction_code'=>$data['transaction_code'],'paid_at'=>$data['status']==='thanh_cong'?now():null]);
                if($data['status']==='thanh_cong')$booking->update(['status'=>'da_thanh_toan']);
                return 'ok';
            },3);
            return response()->json(['result'=>$result],$result==='ok'||$result==='already_processed'?200:409);
        }catch(\Throwable $e){ report($e); return response()->json(['error'=>'Payment processing failed'],500); }
    }
}
