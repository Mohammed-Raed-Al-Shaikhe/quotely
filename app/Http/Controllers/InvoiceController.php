<?php
namespace App\Http\Controllers;
use App\Models\{Invoice,Quote};
use Illuminate\Support\Facades\DB;
use App\Support\Audit;
class InvoiceController extends Controller {
    public function store(Quote $quote){abort_unless($quote->company_id===auth()->user()->company_id,404);abort_unless($quote->status==='accepted',422,'Only accepted quotations can be invoiced.');$invoice=DB::transaction(function()use($quote){$next=(int)Invoice::where('company_id',$quote->company_id)->selectRaw("MAX(CAST(SUBSTR(number, 5) AS INTEGER)) as max_number")->value('max_number')+1;return Invoice::firstOrCreate(['quote_id'=>$quote->id],['company_id'=>$quote->company_id,'customer_id'=>$quote->customer_id,'number'=>'INV-'.str_pad((string)$next,4,'0',STR_PAD_LEFT),'due_date'=>now()->addDays(14),'total_cents'=>$quote->total_cents,'currency'=>$quote->currency]);});Audit::record('invoice.created',$invoice,['quote_id'=>$quote->id]);return redirect()->route('quotes.show',$quote)->with('success','Invoice '.$invoice->number.' created.');}
}
