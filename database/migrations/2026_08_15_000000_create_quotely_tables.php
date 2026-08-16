<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('email');
            $table->string('currency', 3)->default('USD'); $table->timestamps();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
        });
        Schema::create('customers', function (Blueprint $table) {
            $table->id(); $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name'); $table->string('email'); $table->string('company_name')->nullable();
            $table->string('phone')->nullable(); $table->timestamps();
        });
        Schema::create('quotes', function (Blueprint $table) {
            $table->id(); $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('number'); $table->string('title'); $table->string('status')->default('draft');
            $table->date('valid_until'); $table->unsignedInteger('subtotal_cents')->default(0);
            $table->unsignedInteger('tax_cents')->default(0); $table->unsignedInteger('total_cents')->default(0);
            $table->string('currency', 3)->default('USD'); $table->text('notes')->nullable();
            $table->uuid('approval_token')->unique(); $table->timestamp('sent_at')->nullable();
            $table->timestamp('viewed_at')->nullable(); $table->timestamp('decided_at')->nullable();
            $table->string('decision_name')->nullable(); $table->string('decision_ip')->nullable();
            $table->text('decision_comment')->nullable(); $table->timestamps();
            $table->unique(['company_id', 'number']);
        });
        Schema::create('quote_items', function (Blueprint $table) {
            $table->id(); $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->string('description'); $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('unit_price_cents'); $table->unsignedInteger('total_cents'); $table->timestamps();
        });
        Schema::create('invoices', function (Blueprint $table) {
            $table->id(); $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quote_id')->unique()->constrained()->restrictOnDelete();
            $table->string('number'); $table->string('status')->default('unpaid');
            $table->date('due_date'); $table->unsignedInteger('total_cents');
            $table->string('currency', 3); $table->timestamp('paid_at')->nullable(); $table->timestamps();
            $table->unique(['company_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices'); Schema::dropIfExists('quote_items'); Schema::dropIfExists('quotes');
        Schema::dropIfExists('customers'); Schema::table('users', fn (Blueprint $t) => $t->dropConstrainedForeignId('company_id'));
        Schema::dropIfExists('companies');
    }
};
