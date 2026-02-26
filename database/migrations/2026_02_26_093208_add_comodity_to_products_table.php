<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'commodity_code')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->string('commodity_code')->nullable()->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('products', 'commodity_code')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropColumn('commodity_code');
            });
        }
    }
};
