<?php
namespace App\Http\Controllers;
use App\Models\{Company,User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,DB,Hash};
use Illuminate\Support\Str;
class AuthController extends Controller {
    public function login(){return view('auth.login');}
    public function authenticate(Request $r){$data=$r->validate(['email'=>'required|email','password'=>'required','remember'=>'nullable|boolean']);if(!Auth::attempt(['email'=>$data['email'],'password'=>$data['password']],(bool)($data['remember']??false))){return back()->withErrors(['email'=>'The supplied credentials are incorrect.'])->onlyInput('email');}$r->session()->regenerate();$r->user()->update(['last_login_at'=>now()]);return redirect()->intended(route('dashboard'));}
    public function register(){return view('auth.register');}
    public function store(Request $r){$data=$r->validate(['company'=>'required|max:120','name'=>'required|max:100','email'=>'required|email|unique:users','password'=>'required|min:10|confirmed']);$user=DB::transaction(function()use($data){$company=Company::create(['name'=>$data['company'],'slug'=>Str::slug($data['company']).'-'.Str::lower(Str::random(5)),'email'=>$data['email'],'currency'=>'USD','trial_ends_at'=>now()->addDays(14)]);return User::create(['company_id'=>$company->id,'role'=>'owner','name'=>$data['name'],'email'=>$data['email'],'password'=>Hash::make($data['password'])]);});Auth::login($user);$r->session()->regenerate();return redirect()->route('dashboard')->with('success','Your 14-day trial has started.');}
    public function logout(Request $r){Auth::logout();$r->session()->invalidate();$r->session()->regenerateToken();return redirect()->route('login');}
}
