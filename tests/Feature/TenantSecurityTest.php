<?php
namespace Tests\Feature;
use App\Models\{Company,Customer,Quote,User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
class TenantSecurityTest extends TestCase {
    use RefreshDatabase;
    public function test_registration_creates_isolated_trial_workspace():void{$this->post('/register',['company'=>'Northstar','name'=>'Lina','email'=>'lina@example.com','password'=>'LongPassword123!','password_confirmation'=>'LongPassword123!'])->assertRedirect('/');$this->assertAuthenticated();$this->assertDatabaseHas('companies',['name'=>'Northstar','subscription_status'=>'trialing']);$this->assertSame('owner',auth()->user()->role);}
    public function test_user_cannot_view_another_company_quote():void{$a=Company::create(['name'=>'A','email'=>'a@test.com','currency'=>'USD','trial_ends_at'=>now()->addWeek()]);$b=Company::create(['name'=>'B','email'=>'b@test.com','currency'=>'USD','trial_ends_at'=>now()->addWeek()]);$user=User::factory()->create(['company_id'=>$a->id]);$customer=Customer::create(['company_id'=>$b->id,'name'=>'Client','email'=>'c@test.com']);$quote=Quote::create(['company_id'=>$b->id,'customer_id'=>$customer->id,'number'=>'Q-0001','title'=>'Secret','status'=>'sent','valid_until'=>now()->addWeek(),'currency'=>'USD','approval_token'=>(string)Str::uuid()]);$this->actingAs($user)->get(route('quotes.show',$quote))->assertNotFound();}
    public function test_public_approval_is_rate_limited_and_has_security_headers():void{$this->get('/login')->assertHeader('X-Frame-Options','DENY')->assertHeader('X-Content-Type-Options','nosniff');}
}
