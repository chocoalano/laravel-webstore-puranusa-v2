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
        if (! Schema::hasTable('customer_addresses')) {
            return;
        }

        Schema::table('customer_addresses', function (Blueprint $table) {
            if (! Schema::hasColumn('customer_addresses', 'district')) {
                $table->string('district')->nullable()->after('city_id');
            }

            if (! Schema::hasColumn('customer_addresses', 'district_lion')) {
                $table->string('district_lion')->nullable()->after('district');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('customer_addresses')) {
            return;
        }

        Schema::table('customer_addresses', function (Blueprint $table) {
            if (Schema::hasColumn('customer_addresses', 'district_lion')) {
                $table->dropColumn('district_lion');
            }

            if (Schema::hasColumn('customer_addresses', 'district')) {
                $table->dropColumn('district');
            }
        });
    }
};
