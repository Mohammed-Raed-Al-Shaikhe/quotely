<?php
namespace App\Http\Controllers;
use App\Support\Audit;
use Illuminate\Http\Request;
class SettingsController extends Controller {public function edit(){abort_unless(auth()->user()->isAdmin(),403);return view('settings',['company'=>auth()->user()->company]);}public function update(Request $r){abort_unless(auth()->user()->isAdmin(),403);$c=auth()->user()->company;$c->update($r->validate(['name'=>'required|max:120','email'=>'required|email','currency'=>'required|size:3','tax_name'=>'required|max:40','tax_rate'=>'required|integer|min:0|max:100','address'=>'nullable|max:500']));Audit::record('company.updated',$c);return back()->with('success','Workspace settings saved.');}}
