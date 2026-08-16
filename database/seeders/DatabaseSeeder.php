<?php
namespace Database\Seeders;
use App\Models\{Company,Customer,Quote};
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder {
    public function run(): void {
        $company=Company::create(['name'=>'Acme Studio','slug'=>'acme-studio','email'=>'hello@acmestudio.test','currency'=>'USD','trial_ends_at'=>now()->addDays(14)]);
        User::create(['company_id'=>$company->id,'role'=>'owner','name'=>'Demo Owner','email'=>'owner@quotely.test','password'=>Hash::make('Password123!')]);
        $customer=Customer::create(['company_id'=>$company->id,'name'=>'Lina Haddad','email'=>'lina@northstar.test','company_name'=>'Northstar Coffee']);
        Customer::create(['company_id'=>$company->id,'name'=>'Omar Saleh','email'=>'omar@atlas.test','company_name'=>'Atlas Properties']);
        $quote=Quote::create(['company_id'=>$company->id,'customer_id'=>$customer->id,'number'=>'Q-0001','title'=>'Brand website redesign','status'=>'sent','valid_until'=>now()->addDays(12),'subtotal_cents'=>500000,'tax_cents'=>80000,'total_cents'=>580000,'currency'=>'USD','notes'=>'50% deposit to begin. Estimated delivery: six weeks.','approval_token'=>(string)Str::uuid(),'sent_at'=>now()]);
        $quote->items()->createMany([
            ['description'=>'Discovery, research, and UX design','quantity'=>1,'unit_price_cents'=>180000,'total_cents'=>180000],
            ['description'=>'Responsive website development','quantity'=>1,'unit_price_cents'=>270000,'total_cents'=>270000],
            ['description'=>'Launch and team training','quantity'=>1,'unit_price_cents'=>50000,'total_cents'=>50000],
        ]);
    }
}
