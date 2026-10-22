<?php
namespace App\Http\Controllers;
use App\Models\{Category,Movie,Room,Showtime};
use Illuminate\Http\Request;
class MovieController extends Controller
{
    public function home(){
        $featured=Movie::where('status','dang_chieu')->latest('id')->first();
        $movies=Movie::with('categories')->where('status','!=','da_ngung')->latest('id')->take(8)->get();
        $upcoming=Showtime::with(['movie','room'])->where('status','dang_hoat_dong')->where('show_date','>=',today()->toDateString())->orderBy('show_date')->orderBy('start_time')->take(9)->get()->filter(fn($s)=>$s->starts_at->isFuture());
        return view('movies.home',compact('featured','movies','upcoming'));
    }
    public function index(Request $request){
        $query=Movie::with('categories')->where('status','!=','da_ngung');
        if($request->filled('q'))$query->where('title','like','%'.$request->input('q').'%');
        if($request->filled('status')&&in_array($request->input('status'),['dang_chieu','sap_chieu']))$query->where('status',$request->input('status'));
        if($request->filled('category'))$query->whereHas('categories',fn($q)=>$q->where('categories.id',$request->integer('category')));
        $movies=$query->latest()->paginate(8)->withQueryString(); $categories=Category::orderBy('name')->get();
        return view('movies.index',compact('movies','categories'));
    }
    public function show(Movie $movie){
        $movie->load('categories');
        $dates=collect(range(0,6))->map(fn($i)=>today()->addDays($i));
        $showtimes=Showtime::with('room')->where('movie_id',$movie->id)->where('status','dang_hoat_dong')->whereBetween('show_date',[$dates->first()->toDateString(),$dates->last()->toDateString()])->orderBy('show_date')->orderBy('start_time')->get()->filter(fn($s)=>$s->starts_at->isFuture())->groupBy(fn($s)=>$s->show_date->format('Y-m-d'));
        return view('movies.show',compact('movie','dates','showtimes'));
    }
    public function rooms(){
        $rooms=Room::withCount('seats')->get();
        return view('movies.rooms',compact('rooms'));
    }
    public function room(Room $room){
        $room->loadCount('seats');
        $showtimes=Showtime::with('movie')->where('room_id',$room->id)->where('status','dang_hoat_dong')->whereDate('show_date','>=',today())->orderBy('show_date')->orderBy('start_time')->get()->filter(fn($s)=>$s->starts_at->isFuture())->take(40);
        return view('movies.room',compact('room','showtimes'));
    }
}
