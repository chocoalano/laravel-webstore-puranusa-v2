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
        Schema::table('whatsapp_broadcasts', function (Blueprint $table): void {
            $table->json('body_params')->nullable()->after('message')
                ->comment('Pemetaan variabel template ke kolom customers, e.g. [{value:"full_name",value_text:"customers.name"}]');
            $table->text('message')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_broadcasts', function (Blueprint $table): void {
            $table->dropColumn('body_params');
            $table->text('message')->nullable(false)->change();
        });
    }
};
