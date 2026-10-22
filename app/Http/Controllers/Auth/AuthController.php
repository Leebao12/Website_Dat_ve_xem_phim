<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
class AuthController extends Controller
{
    public function loginForm(){return view('auth.login');}
    public function registerForm(){return view('auth.register');}
    public function login(Request $request){
        $data=$request->validate(['email'=>['required','email'],'password'=>['required','string']]);
        $user=User::where('email',$data['email'])->first();
        if($user && $user->status!=='hoat_dong') return back()->withErrors(['email'=>'Tài khoản đã bị khóa.'])->onlyInput('email');
        if(!Auth::attempt($data)) return back()->withErrors(['email'=>'Email hoặc mật khẩu không đúng.'])->onlyInput('email');
        $request->session()->regenerate();
        return redirect()->intended(match(Auth::user()->role){'admin'=>route('admin.users.index'),'quan_ly'=>route('manager.dashboard'),default=>route('movies.index')});
    }
    public function register(Request $request){
        $data=$request->validate(['name'=>['required','string','max:255'],'email'=>['required','email','max:255','unique:users,email'],'phone'=>['nullable','string','max:20'],'password'=>['required','confirmed',Password::min(8)]]);
        $user=User::create(['name'=>$data['name'],'email'=>$data['email'],'phone'=>$data['phone']??null,'password'=>$data['password'],'role'=>'khach_hang','status'=>'hoat_dong']);
        Auth::login($user); $request->session()->regenerate();
        return redirect()->route('movies.index')->with('success','Tạo tài khoản thành công.');
    }
    public function logout(Request $request){ Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect()->route('home'); }
}
