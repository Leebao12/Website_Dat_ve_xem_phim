<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
class UserController extends Controller
{
    public function index(Request $r){$q=User::query();
        if($r->filled('q'))$q->where(fn($qb)=>$qb->where('name','like','%'.$r->q.'%')->orWhere('email','like','%'.$r->q.'%'));
        if($r->filled('role')&&in_array($r->role,['khach_hang','quan_ly','admin']))$q->where('role',$r->role);
        return view('admin.users', ['users'=>$q->latest()->paginate(15)->withQueryString()]);}
    public function update(Request $r,User $user){
        $data=$r->validate(['role'=>['required','in:khach_hang,quan_ly,admin'],'status'=>['required','in:hoat_dong,khoa']]);
        if($user->id===$r->user()->id &&($data['status']!=='hoat_dong'||$data['role']!=='admin'))return back()->with('error','Không thể tự khóa hoặc hạ quyền tài khoản Admin đang đăng nhập.');
        $user->update($data);return back()->with('success','Đã cập nhật tài khoản.');
    }
}
