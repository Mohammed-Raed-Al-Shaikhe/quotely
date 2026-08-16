<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasIndex('quotes', ['company_id', 'parent_quote_id', 'version'])) {
            Schema::table('quotes', function (Blueprint $table) {
                $table->index(['company_id', 'parent_quote_id', 'version']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'parent_quote_id', 'version']);
        });
    }
};
