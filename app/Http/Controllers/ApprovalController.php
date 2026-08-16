<?php
namespace App\Http\Controllers;
use App\Models\Quote;
use Illuminate\Http\Request;
use App\Support\Audit;
class ApprovalController extends Controller {
    public function show(string $token){$quote=Quote::where('approval_token',$token)->where('status','!=','draft')->with('company','customer','items')->firstOrFail();if(!$quote->viewed_at)$quote->update(['viewed_at'=>now()]);return view('approval',['quote'=>$quote]);}
    public function decide(Request $request,string $token){
        $data=$request->validate(['decision'=>'required|in:accepted,rejected,changes_requested','name'=>'required|max:100','comment'=>'nullable|max:1000']);
        $quote=Quote::where('approval_token',$token)->where('status','!=','draft')->firstOrFail(); abort_if($quote->decided_at,409,'This quotation has already been decided.');
        $quote->update(['status'=>$data['decision'],'decision_name'=>$data['name'],'decision_comment'=>$data['comment']??null,'decision_ip'=>$request->ip(),'decided_at'=>now()]); Audit::record('quote.'.$data['decision'],$quote,['decision_name'=>$data['name']]);
        return redirect()->route('approval.show',$token)->with('success','Your decision has been recorded.');
    }
}
