<?php
namespace App\Http\Controllers;
use App\Models\Customer;
use App\Support\Audit;
use Illuminate\Http\Request;
class CustomerController extends Controller {
    public function index(){return view('customers.index',['customers'=>auth()->user()->company->customers()->latest()->paginate(20)]);}
    public function create(){return view('customers.form',['customer'=>new Customer]);}
    public function store(Request $r){$customer=auth()->user()->company->customers()->create($this->validated($r));Audit::record('customer.created',$customer);return redirect()->route('customers.index')->with('success','Customer created.');}
    public function edit(Customer $customer){$this->own($customer);return view('customers.form',compact('customer'));}
    public function update(Request $r,Customer $customer){$this->own($customer);$customer->update($this->validated($r));Audit::record('customer.updated',$customer);return redirect()->route('customers.index')->with('success','Customer updated.');}
    public function destroy(Customer $customer){$this->own($customer);abort_if($customer->quotes()->exists(),422,'Customers with quotations cannot be deleted.');$customer->delete();Audit::record('customer.deleted',$customer);return back()->with('success','Customer deleted.');}
    private function own(Customer $c):void{abort_unless($c->company_id===auth()->user()->company_id,404);} private function validated(Request $r):array{return $r->validate(['name'=>'required|max:120','email'=>'required|email|max:190','company_name'=>'nullable|max:120','phone'=>'nullable|max:40']);}
}
