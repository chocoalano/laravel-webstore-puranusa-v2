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
        Schema::table('whatsapp_broadcast_recipients', function (Blueprint $table): void {
            $table->enum('status', ['queued', 'processing', 'pending', 'sent', 'failed'])
                ->default('queued')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_broadcast_recipients', function (Blueprint $table): void {
            $table->enum('status', ['pending', 'sent', 'failed'])
                ->default('pending')
                ->change();
        });
    }
};
