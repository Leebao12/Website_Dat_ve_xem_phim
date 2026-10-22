<?php
namespace App\Http\Controllers\Manager;
use App\Http\Controllers\Controller;
use App\Models\{Movie,Category};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class MovieController extends Controller
{
    public function index(Request $r){
        $q=Movie::with('categories');if($r->filled('q'))$q->where('title','like','%'.$r->q.'%');
        $movies=$q->latest()->paginate(10)->withQueryString();return view('manager.movies.index',compact('movies'));
    }
    public function create(){return view('manager.movies.form',['movie'=>new Movie,'categories'=>Category::orderBy('name')->get()]);}
    public function edit(Movie $movie){$movie->load('categories');return view('manager.movies.form',['movie'=>$movie,'categories'=>Category::orderBy('name')->get()]);}
    private function validated(Request $r): array{return $r->validate([
        'title'=>['required','string','max:255'],'description'=>['nullable','string'], 'duration'=>['required','integer','min:1','max:500'],
        'release_date'=>['required','date'],'status'=>['required','in:sap_chieu,dang_chieu,da_ngung'],
        'poster_file'=>['nullable','image','max:4096'],'trailer'=>['nullable','url','max:255'],
        'categories'=>['required','array','min:1'],'categories.*'=>['integer','exists:categories,id']]);}
    public function store(Request $r){$data=$this->validated($r);$ids=$data['categories'];unset($data['categories'],$data['poster_file']);
        if($r->hasFile('poster_file'))$data['poster']=$r->file('poster_file')->store('movies','public');
        $movie=Movie::create($data);$movie->categories()->sync($ids);return redirect()->route('manager.movies.index')->with('success','Đã thêm phim.');}
    public function update(Request $r,Movie $movie){$data=$this->validated($r);$ids=$data['categories'];unset($data['categories'],$data['poster_file']);
        if($r->hasFile('poster_file')){if($movie->poster && !str_starts_with($movie->poster,'assets/'))Storage::disk('public')->delete($movie->poster);$data['poster']=$r->file('poster_file')->store('movies','public');}
        $movie->update($data);$movie->categories()->sync($ids);return redirect()->route('manager.movies.index')->with('success','Đã cập nhật phim.');}
    public function destroy(Movie $movie){
        if($movie->showtimes()->exists())return back()->with('error','Không thể xóa phim đã có suất chiếu.');
        if($movie->poster && !str_starts_with($movie->poster,'assets/'))Storage::disk('public')->delete($movie->poster);
        $movie->delete();return back()->with('success','Đã xóa phim.');
    }
}
