<?php
namespace Tests\Feature;
use App\Models\{Company,Customer,Invoice,Quote,User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
class QuoteWorkflowTest extends TestCase {
    use RefreshDatabase;
    private function quote(string $status='sent'): Quote {
        $company=Company::create(['name'=>'Studio','email'=>'studio@test.com','currency'=>'USD','trial_ends_at'=>now()->addWeek()]);
        $this->actingAs(User::factory()->create(['company_id'=>$company->id,'role'=>'owner']));
        $customer=Customer::create(['company_id'=>$company->id,'name'=>'Client','email'=>'client@test.com']);
        return Quote::create(['company_id'=>$company->id,'customer_id'=>$customer->id,'number'=>'Q-0001','title'=>'Project','status'=>$status,'valid_until'=>now()->addWeek(),'subtotal_cents'=>10000,'tax_cents'=>1600,'total_cents'=>11600,'currency'=>'USD','approval_token'=>(string)Str::uuid()]);
    }
    public function test_customer_can_accept_a_quote(): void {
        $quote=$this->quote();
        $this->post(route('approval.decide',$quote->approval_token),['decision'=>'accepted','name'=>'Jane Client','comment'=>'Approved'])->assertRedirect();
        $this->assertDatabaseHas('quotes',['id'=>$quote->id,'status'=>'accepted','decision_name'=>'Jane Client']);
    }
    public function test_decision_cannot_be_replaced(): void {
        $quote=$this->quote('accepted'); $quote->update(['decided_at'=>now()]);
        $this->post(route('approval.decide',$quote->approval_token),['decision'=>'rejected','name'=>'Someone'])->assertStatus(409);
    }
    public function test_accepted_quote_converts_to_only_one_invoice(): void {
        $quote=$this->quote('accepted');
        $this->post(route('invoices.store',$quote))->assertRedirect(); $this->post(route('invoices.store',$quote))->assertRedirect();
        $this->assertSame(1,Invoice::where('quote_id',$quote->id)->count());
    }
    public function test_unaccepted_quote_cannot_be_invoiced(): void {
        $this->post(route('invoices.store',$this->quote()))->assertStatus(422);
    }
}
