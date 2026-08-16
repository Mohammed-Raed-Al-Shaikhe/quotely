<?php
namespace App\Http\Controllers;
use App\Models\Product;
use App\Support\Audit;
use Illuminate\Http\Request;
class ProductController extends Controller {
    public function index(){return view('products.index',['products'=>auth()->user()->company->products()->latest()->paginate(20)]);}
    public function create(){return view('products.form',['product'=>new Product]);}
    public function store(Request $r){$d=$this->data($r);$d['unit_price_cents']=(int)round($d['unit_price']*100);unset($d['unit_price']);$p=auth()->user()->company->products()->create($d);Audit::record('product.created',$p);return redirect()->route('products.index')->with('success','Item created.');}
    public function edit(Product $product){$this->own($product);return view('products.form',compact('product'));}
    public function update(Request $r,Product $product){$this->own($product);$d=$this->data($r);$d['unit_price_cents']=(int)round($d['unit_price']*100);unset($d['unit_price']);$product->update($d);Audit::record('product.updated',$product);return redirect()->route('products.index')->with('success','Item updated.');}
    public function destroy(Product $product){$this->own($product);$product->delete();Audit::record('product.deleted',$product);return back()->with('success','Item archived.');}
    private function own(Product $p):void{abort_unless($p->company_id===auth()->user()->company_id,404);}private function data(Request $r):array{return $r->validate(['name'=>'required|max:120','description'=>'nullable|max:500','unit_price'=>'required|numeric|min:0|max:999999','active'=>'nullable|boolean']);}
}
