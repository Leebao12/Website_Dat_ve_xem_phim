<?php
namespace App\Http\Controllers\Manager;
use App\Http\Controllers\Controller;
use App\Models\{Room,Seat};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
class RoomController extends Controller
{
    public function index(){return view('manager.rooms.index',['rooms'=>Room::withCount(['seats','showtimes'])->orderBy('name')->paginate(12)]);}
    public function show(Room $room){$room->load(['seats'=>fn($q)=>$q->orderBy('seat_code')]);return view('manager.rooms.show',compact('room'));}
    public function store(Request $r){$d=$r->validate(['name'=>['required','string','max:50','unique:rooms,name']]);$room=Room::create($d+['total_seats'=>0]);return redirect()->route('manager.rooms.show',$room)->with('success','Đã tạo phòng. Hãy cấu hình ghế.');}
    public function update(Request $r,Room $room){$d=$r->validate(['name'=>['required','string','max:50',Rule::unique('rooms','name')->ignore($room->id)]]);$room->update($d);return back()->with('success','Đã sửa tên phòng.');}
    public function destroy(Room $room){if($room->showtimes()->exists())return back()->with('error','Không thể xóa phòng đã có suất chiếu liên kết.');DB::transaction(function()use($room){$room->seats()->delete();$room->delete();});return redirect()->route('manager.rooms.index')->with('success','Đã xóa phòng.');}
    public function addSeat(Request $r,Room $room){
        $d=$r->validate(['seat_code'=>['required','string','max:10',Rule::unique('seats','seat_code')->where('room_id',$room->id)],'type'=>['required','in:thuong,vip'],'price'=>['required','numeric','min:0','max:99999999']]);
        DB::transaction(function()use($room,$d){$room=Room::lockForUpdate()->findOrFail($room->id);$room->seats()->create($d);$room->update(['total_seats'=>$room->seats()->count()]);});return back()->with('success','Đã thêm ghế.');
    }
    public function updateSeat(Request $r,Room $room,Seat $seat){
        abort_unless((int)$seat->room_id===(int)$room->id,404);
        $d=$r->validate(['seat_code'=>['required','string','max:10',Rule::unique('seats','seat_code')->where('room_id',$room->id)->ignore($seat->id)],'type'=>['required','in:thuong,vip'],'price'=>['required','numeric','min:0','max:99999999']]);
        // Seat price/type may change for future orders; existing booking_seats.price remains unchanged.
        $seat->update($d);return back()->with('success','Đã sửa ghế.');
    }
    public function deleteSeat(Room $room,Seat $seat){
        abort_unless((int)$seat->room_id===(int)$room->id,404);
        if($seat->bookingSeats()->exists())return back()->with('error','Ghế đã từng được đặt, không thể xóa để bảo toàn lịch sử.');
        DB::transaction(function()use($room,$seat){$locked=Room::lockForUpdate()->findOrFail($room->id);$seat->delete();$locked->update(['total_seats'=>$locked->seats()->count()]);});
        return back()->with('success','Đã xóa ghế.');
    }
}
