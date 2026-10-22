<?php
namespace App\Http\Controllers\Manager;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class CategoryController extends Controller
{
    public function index(){return view('manager.categories.index',['categories'=>Category::withCount('movies')->orderBy('name')->paginate(15)]);}
    public function store(Request $r){$d=$r->validate(['name'=>['required','string','max:100','unique:categories,name']]);Category::create($d);return back()->with('success','Đã thêm thể loại.');}
    public function update(Request $r,Category $category){$d=$r->validate(['name'=>['required','string','max:100',Rule::unique('categories','name')->ignore($category->id)]]);$category->update($d);return back()->with('success','Đã sửa thể loại.');}
    public function destroy(Category $category){if($category->movies()->exists())return back()->with('error','Thể loại đang gắn với phim, không thể xóa.');$category->delete();return back()->with('success','Đã xóa thể loại.');}
}
