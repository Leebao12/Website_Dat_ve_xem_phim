<?php
namespace App\Http\Controllers\Manager;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class ProductController extends Controller
{
    public function index(){return view('manager.products.index',['products'=>Product::latest()->paginate(12)]);}
    public function create(){return view('manager.products.form',['product'=>new Product]);}
    public function edit(Product $product){return view('manager.products.form',compact('product'));}
    private function checked(Request $r):array{return $r->validate(['name'=>['required','string','max:255'],'description'=>['nullable','string'],'price'=>['required','numeric','min:0','max:99999999'],'image_file'=>['nullable','image','max:4096'],'status'=>['required','in:dang_ban,ngung_ban']]);}
    public function store(Request $r){$d=$this->checked($r);unset($d['image_file']);if($r->hasFile('image_file'))$d['image']=$r->file('image_file')->store('products','public');Product::create($d);return redirect()->route('manager.products.index')->with('success','Đã thêm sản phẩm.');}
    public function update(Request $r,Product $product){$d=$this->checked($r);unset($d['image_file']);if($r->hasFile('image_file')){if($product->image&&!str_starts_with($product->image,'assets/'))Storage::disk('public')->delete($product->image);$d['image']=$r->file('image_file')->store('products','public');}$product->update($d);return redirect()->route('manager.products.index')->with('success','Đã sửa sản phẩm.');}
    public function destroy(Product $product){if($product->exists()&&\App\Models\BookingProduct::where('product_id',$product->id)->exists())return back()->with('error','Sản phẩm đã xuất hiện trong đơn hàng. Hãy ngừng bán thay vì xóa.');$product->delete();return back()->with('success','Đã xóa sản phẩm.');}
}
