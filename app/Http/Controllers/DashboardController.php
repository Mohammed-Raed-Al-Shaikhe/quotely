<?php
namespace App\Http\Controllers;
use App\Models\{Customer, Invoice, Quote};
class DashboardController extends Controller {
    public function __invoke(){
        $companyId=auth()->user()->company_id;
        return view('dashboard', [
            'quotes'=>Quote::where('company_id',$companyId)->with('customer')->latest()->limit(20)->get(), 'customers'=>Customer::where('company_id',$companyId)->count(),
            'accepted'=>Quote::where('company_id',$companyId)->where('status','accepted')->count(),
            'outstanding'=>Invoice::where('company_id',$companyId)->where('status','unpaid')->sum('total_cents'),
        ]);
    }
}
