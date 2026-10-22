<?php
namespace App\Http\Controllers\Manager;
use App\Http\Controllers\Controller;
use App\Models\{Movie,Room,Showtime};
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class ShowtimeController extends Controller
{
    public function index(Request $r){
        $q=Showtime::with(['movie','room'])->orderByDesc('show_date')->orderBy('start_time');
        if($r->filled('date'))$q->whereDate('show_date',$r->date);
        if($r->filled('room_id'))$q->where('room_id',$r->integer('room_id'));
        return view('manager.showtimes.index',['showtimes'=>$q->paginate(15)->withQueryString(),'rooms'=>Room::orderBy('name')->get()]);
    }
    public function create(){return view('manager.showtimes.form',['showtime'=>new Showtime,'movies'=>Movie::where('status','!=','da_ngung')->orderBy('title')->get(),'rooms'=>Room::orderBy('name')->get()]);}
    public function edit(Showtime $showtime){return view('manager.showtimes.form',['showtime'=>$showtime,'movies'=>Movie::where('status','!=','da_ngung')->orderBy('title')->get(),'rooms'=>Room::orderBy('name')->get()]);}
    private function checked(Request $r,?Showtime $showtime=null): array{
        $d=$r->validate(['movie_id'=>['required','integer','exists:movies,id'],'room_id'=>['required','integer','exists:rooms,id'],'show_date'=>['required','date'],'start_time'=>['required','date_format:H:i'],'status'=>['required','in:dang_hoat_dong,da_dong,da_huy']]);
        $movie=Movie::findOrFail($d['movie_id']);$room=Room::findOrFail($d['room_id']);
        if($room->seats()->count()===0)throw ValidationException::withMessages(['room_id'=>'Phòng chưa có ghế.']);
        $start=Carbon::parse($d['show_date'].' '.$d['start_time']);
        if(!$showtime && $start->isPast())throw ValidationException::withMessages(['show_date'=>'Không thể tạo lịch chiếu trong quá khứ.']);
        if($showtime && $showtime->bookings()->exists() && ($showtime->movie_id!=$d['movie_id'] ||$showtime->room_id!=$d['room_id']||$showtime->show_date->format('Y-m-d')!==$d['show_date']||substr($showtime->start_time,0,5)!==$d['start_time'])) throw ValidationException::withMessages(['show_date'=>'Suất chiếu đã có đơn đặt vé, không thể thay đổi phim/phòng/giờ.']);
        if($showtime && $d['status']==='da_huy' && $showtime->bookings()->where('status','da_thanh_toan')->exists())throw ValidationException::withMessages(['status'=>'Không được hủy suất chiếu đã bán vé thành công.']);
        $end=$start->copy()->addMinutes($movie->duration+15);
        $others=Showtime::with('movie')->where('room_id',$room->id)->where('status','!=','da_huy')->whereBetween('show_date',[$start->copy()->subDay()->toDateString(),$end->copy()->toDateString()])->get();
        foreach($others as $other){
            if($showtime && $other->id===$showtime->id)continue;
            $oldStart=$other->starts_at;$oldEnd=$oldStart->copy()->addMinutes($other->movie->duration+15);
            if($start->lt($oldEnd)&&$oldStart->lt($end))throw ValidationException::withMessages(['start_time'=>'Phòng này bị trùng lịch với suất chiếu #'.$other->id.' (bao gồm 15 phút dọn phòng).']);
        }
        return $d;
    }
    public function store(Request $r){
        // Serialize edits to room schedule on MySQL via room row lock.
        $roomId=$r->integer('room_id');
        DB::transaction(function()use($r,$roomId){Room::whereKey($roomId)->lockForUpdate()->first();$d=$this->checked($r);Showtime::create($d);},3);
        return redirect()->route('manager.showtimes.index')->with('success','Đã tạo suất chiếu.');
    }
    public function update(Request $r,Showtime $showtime){
        DB::transaction(function()use($r,$showtime){
            // Lock both involved rooms in deterministic order when moving a showtime.
            Room::whereIn('id',[$showtime->room_id,$r->integer('room_id')])->orderBy('id')->lockForUpdate()->get();
            $d=$this->checked($r,$showtime);$showtime->update($d);
        },3);
        return redirect()->route('manager.showtimes.index')->with('success','Đã cập nhật suất chiếu.');
    }
    public function destroy(Showtime $showtime){if($showtime->bookings()->exists())return back()->with('error','Không thể xóa suất chiếu đã có đơn đặt vé.');$showtime->delete();return back()->with('success','Đã xóa suất chiếu.');}
}
