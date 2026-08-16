<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('quotes', 'parent_quote_id')) {
            Schema::table('quotes', function (Blueprint $table) {
                $table->foreignId('parent_quote_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('quotes')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('quotes', 'parent_quote_id')) {
            Schema::table('quotes', function (Blueprint $table) {
                $table->dropConstrainedForeignId('parent_quote_id');
            });
        }
    }
};
