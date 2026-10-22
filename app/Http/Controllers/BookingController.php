<?php
namespace App\Http\Controllers;
use App\Models\{Booking,BookingProduct,BookingSeat,Payment,Product,Seat,Showtime};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class BookingController extends Controller
{
    public function create(Showtime $showtime){
        abort_unless($showtime->status==='dang_hoat_dong' && $showtime->starts_at->isFuture(),404);
        $showtime->load(['movie','room.seats']);
        $taken=$this->takenSeatIds($showtime);
        $products=Product::where('status','dang_ban')->orderBy('price')->get();
        return view('booking.create',compact('showtime','taken','products'));
    }
    private function takenSeatIds(Showtime $showtime): array {
        return BookingSeat::whereHas('booking',function($q) use($showtime){
            $q->where('showtime_id',$showtime->id)->where(function($qq){
                $qq->where('status','da_thanh_toan')->orWhere(fn($pending)=>$pending->where('status','cho_thanh_toan')->where('created_at','>=',now()->subMinutes(config('cinemax.hold_minutes'))));
            });
        })->pluck('seat_id')->map(fn($id)=>(int)$id)->all();
    }
    public function store(Request $request, Showtime $showtime){
        abort_unless($request->user()->role==='khach_hang',403,'Chỉ khách hàng mới có thể đặt vé.');
        $data=$request->validate([
            'seats'=>['required','array','min:1','max:8'],'seats.*'=>['required','integer','distinct','exists:seats,id'],
            'products'=>['nullable','array'],'products.*'=>['nullable','integer','min:0','max:10']
        ]);
        // A lock on the showtime row serializes all seat reservations for this showtime on MySQL/InnoDB.
        $booking=DB::transaction(function() use($request,$showtime,$data){
            $locked=Showtime::with('movie')->lockForUpdate()->findOrFail($showtime->id);
            if($locked->status!=='dang_hoat_dong'||!$locked->starts_at->isFuture()) throw ValidationException::withMessages(['seats'=>'Suất chiếu này không còn mở đặt vé.']);
            $seatIds=array_map('intval',$data['seats']);
            $seats=Seat::where('room_id',$locked->room_id)->whereIn('id',$seatIds)->get();
            if($seats->count()!==count($seatIds))throw ValidationException::withMessages(['seats'=>'Danh sách ghế không hợp lệ.']);
            $taken=$this->takenSeatIds($locked);
            if(array_intersect($seatIds,$taken))throw ValidationException::withMessages(['seats'=>'Ghế vừa được người khác giữ hoặc mua. Vui lòng chọn lại.']);
            $quantities=[];
            foreach(($data['products']??[]) as $id=>$quantity) if((int)$quantity>0)$quantities[(int)$id]=(int)$quantity;
            $products=Product::where('status','dang_ban')->whereIn('id',array_keys($quantities))->get();
            if(count($quantities)!==$products->count()) throw ValidationException::withMessages(['products'=>'Sản phẩm đi kèm không hợp lệ.']);
            $total=$seats->sum(fn($s)=>(float)$s->price)+$products->sum(fn($p)=>(float)$p->price*$quantities[$p->id]);
            $booking=Booking::create(['booking_code'=>'CMX-'.now()->format('ymd').'-'.strtoupper(Str::random(7)),
                'user_id'=>$request->user()->id,'showtime_id'=>$locked->id,'total_amount'=>$total,'status'=>'cho_thanh_toan']);
            foreach($seats as $seat) BookingSeat::create(['booking_id'=>$booking->id,'seat_id'=>$seat->id,'price'=>$seat->price,'created_at'=>now()]);
            foreach($products as $p) BookingProduct::create(['booking_id'=>$booking->id,'product_id'=>$p->id,'quantity'=>$quantities[$p->id],
                'price'=>$p->price,'subtotal'=>$p->price*$quantities[$p->id],'created_at'=>now()]);
            Payment::create(['booking_id'=>$booking->id,'payment_method'=>'sandbox','amount'=>$total,'status'=>'cho_thanh_toan']);
            return $booking;
        },3);
        return redirect()->route('booking.show',$booking)->with('success','Đã giữ ghế. Hãy thanh toán trong '.config('cinemax.hold_minutes').' phút.');
    }
    public function show(Request $request, Booking $booking){
        abort_unless((int)$booking->user_id===(int)$request->user()->id || in_array($request->user()->role,['quan_ly','admin']),403);
        $booking->load(['user','showtime.movie','showtime.room','bookingSeats.seat','bookingProducts.product','payments']);
        return view('booking.show',compact('booking'));
    }
    public function pay(Request $request,Booking $booking){
        abort_unless((int)$request->user()->id===(int)$booking->user_id && $request->user()->role==='khach_hang',403);
        $result=DB::transaction(function() use($booking){
            $lockedShowtime=Showtime::lockForUpdate()->findOrFail($booking->showtime_id);
            $b=Booking::lockForUpdate()->findOrFail($booking->id);
            if($b->status!=='cho_thanh_toan')return 'processed';
            if($lockedShowtime->status==='da_huy'){$b->update(['status'=>'da_huy']);return 'cancelled';}
            if($b->expired){$b->update(['status'=>'da_huy']); return 'expired';}
            $p=Payment::where('booking_id',$b->id)->where('status','cho_thanh_toan')->lockForUpdate()->first();
            if (!$p) $p=Payment::create(['booking_id'=>$b->id,'payment_method'=>'sandbox','amount'=>$b->total_amount,'status'=>'cho_thanh_toan']);
            $p->update(['status'=>'thanh_cong','transaction_code'=>'SANDBOX-'.strtoupper(Str::random(12)),'paid_at'=>now()]);
            $b->update(['status'=>'da_thanh_toan']); return 'paid';
        },3);
        if($result==='cancelled')return redirect()->route('booking.show',$booking)->with('error','Suất chiếu đã bị hủy. Đơn không thể thanh toán.');
        if($result==='expired') return redirect()->route('booking.show',$booking)->with('error','Đã hết thời gian giữ ghế. Vui lòng đặt vé lại.');
        if($result==='processed')return redirect()->route('booking.show',$booking)->with('error','Đơn này đã được xử lý.');
        return redirect()->route('booking.show',$booking)->with('success','Thanh toán thử nghiệm thành công!');
    }
    public function cancel(Request $request,Booking $booking){
        abort_unless((int)$request->user()->id===(int)$booking->user_id || $request->user()->role==='quan_ly',403);
        DB::transaction(function()use($booking){
            Showtime::whereKey($booking->showtime_id)->lockForUpdate()->first();
            $b=Booking::lockForUpdate()->findOrFail($booking->id);
            if($b->status==='cho_thanh_toan')$b->update(['status'=>'da_huy']);
            else throw ValidationException::withMessages(['booking'=>'Chỉ có thể hủy đơn chưa thanh toán.']);
        });
        return back()->with('success','Đã hủy đơn chưa thanh toán.');
    }
}
