<?php
namespace App\Http\Controllers;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
class AccountController extends Controller
{
    public function profile(Request $request){return view('account.profile',['user'=>$request->user()]);}
    public function update(Request $request){
        $user=$request->user();
        $data=$request->validate(['name'=>['required','string','max:255'],'phone'=>['nullable','string','max:20'],'email'=>['required','email','max:255','unique:users,email,'.$user->id]]);
        $user->update($data); return back()->with('success','Đã cập nhật thông tin cá nhân.');
    }
    public function password(Request $request){
        $data=$request->validate(['current_password'=>['required','current_password'],'password'=>['required','confirmed',Password::min(8)]]);
        $request->user()->update(['password'=>$data['password']]);
        return back()->with('success','Đổi mật khẩu thành công.');
    }
    public function orders(Request $request){
        $bookings=Booking::with(['showtime.movie','showtime.room','bookingSeats.seat'])->where('user_id',$request->user()->id)->latest()->paginate(10);
        return view('account.orders',compact('bookings'));
    }
}
