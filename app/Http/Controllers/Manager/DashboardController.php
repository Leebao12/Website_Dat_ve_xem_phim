<?php
namespace App\Http\Controllers\Manager;
use App\Http\Controllers\Controller;
use App\Models\{Booking,Movie,Room,Showtime};
use Illuminate\Http\Request;
class DashboardController extends Controller
{
    public function index(){
        $stats=['movies'=>Movie::count(),'rooms'=>Room::count(),'showtimes'=>Showtime::whereDate('show_date',today())->count(),
            'bookings'=>Booking::count(),'revenue'=>Booking::where('status','da_thanh_toan')->sum('total_amount')];
        $latest=Booking::with(['user','showtime.movie'])->latest()->take(8)->get();
        return view('manager.dashboard',compact('stats','latest'));
    }
    public function revenue(Request $request){
        $data=$request->validate(['from'=>['nullable','date'],'to'=>['nullable','date','after_or_equal:from']]);
        $from=$data['from']??today()->subDays(29)->toDateString();$to=$data['to']??today()->toDateString();
        $q=Booking::where('status','da_thanh_toan')->whereBetween('updated_at',[$from.' 00:00:00',$to.' 23:59:59']);
        $total=(clone $q)->sum('total_amount'); $count=(clone $q)->count();
        $daily=(clone $q)->selectRaw('DATE(updated_at) day, COUNT(*) total_orders, SUM(total_amount) total')->groupByRaw('DATE(updated_at)')->orderBy('day')->get();
        return view('manager.revenue',compact('from','to','total','count','daily'));
    }
}
