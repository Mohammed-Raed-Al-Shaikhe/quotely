<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('companies', function(Blueprint $t){
            $t->string('slug')->nullable()->unique(); $t->string('plan')->default('trial');
            $t->timestamp('trial_ends_at')->nullable(); $t->string('tax_name')->default('Tax');
            $t->unsignedTinyInteger('tax_rate')->default(16); $t->string('address')->nullable();
            $t->string('stripe_customer_id')->nullable()->index(); $t->string('subscription_status')->default('trialing');
        });
        Schema::table('users', function(Blueprint $t){$t->string('role')->default('member');$t->timestamp('last_login_at')->nullable();});
        Schema::table('customers', function(Blueprint $t){$t->softDeletes();});
        Schema::table('quotes', function(Blueprint $t){$t->unsignedInteger('version')->default(1);$t->softDeletes();});
        Schema::create('products', function(Blueprint $t){$t->id();$t->foreignId('company_id')->constrained()->cascadeOnDelete();$t->string('name');$t->string('description')->nullable();$t->unsignedInteger('unit_price_cents');$t->boolean('active')->default(true);$t->timestamps();$t->softDeletes();});
        Schema::create('audit_logs', function(Blueprint $t){$t->id();$t->foreignId('company_id')->constrained()->cascadeOnDelete();$t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();$t->string('event');$t->string('subject_type');$t->unsignedBigInteger('subject_id');$t->json('metadata')->nullable();$t->string('ip_address')->nullable();$t->timestamps();$t->index(['company_id','created_at']);});
    }
    public function down(): void {Schema::dropIfExists('audit_logs');Schema::dropIfExists('products');Schema::table('quotes',fn(Blueprint $t)=>$t->dropColumn(['version','deleted_at']));Schema::table('customers',fn(Blueprint $t)=>$t->dropColumn('deleted_at'));Schema::table('users',fn(Blueprint $t)=>$t->dropColumn(['role','last_login_at']));Schema::table('companies',fn(Blueprint $t)=>$t->dropColumn(['slug','plan','trial_ends_at','tax_name','tax_rate','address','stripe_customer_id','subscription_status']));}
};
