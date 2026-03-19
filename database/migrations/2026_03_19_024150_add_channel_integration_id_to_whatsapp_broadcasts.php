<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_broadcasts', function (Blueprint $table) {
            $table->string('channel_integration_id', 100)
                ->nullable()
                ->after('template_id')
                ->comment('Qontak channel integration UUID, null = gunakan default dari config');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_broadcasts', function (Blueprint $table) {
            $table->dropColumn('channel_integration_id');
        });
    }
};
