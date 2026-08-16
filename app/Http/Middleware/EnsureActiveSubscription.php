<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class EnsureActiveSubscription {public function handle(Request $request,Closure $next){if(!$request->user()->company->subscriptionIsActive())return redirect()->route('billing')->with('error','Choose a plan to continue using Quotely.');return $next($request);}}
