<?php
namespace App\Http\Controllers;
class BillingController extends Controller {public function index(){return view('billing',['company'=>auth()->user()->company]);}}
