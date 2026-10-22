<?php
namespace App\Http\Controllers\Manager;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
class OrderController extends Controller
{
    public function index(Request $r){
        $q=Booking::with(['user','showtime.movie']);
        if($r->filled('q')){ $s=$r->q;$q->where(fn($builder)=>$builder->where('booking_code','like','%'.$s.'%')->orWhereHas('user',fn($u)=>$u->where('name','like','%'.$s.'%')->orWhere('email','like','%'.$s.'%'))); }
        if($r->filled('status')&&in_array($r->status,['cho_thanh_toan','da_thanh_toan','da_huy']))$q->where('status',$r->status);
        return view('manager.bookings.index',['bookings'=>$q->latest()->paginate(15)->withQueryString()]);
    }
}
