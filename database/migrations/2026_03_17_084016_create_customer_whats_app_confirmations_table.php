<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_whatsapp_confirmations', function (Blueprint $table): void {
            $table->id();

            $table->unsignedInteger('customer_id')
                ->nullable();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->nullOnDelete();

            $table->string('phone', 20)
                ->unique()
                ->comment('Nomor WA yang telah mengirim pesan ke sistem');

            $table->timestamp('confirmed_at')
                ->comment('Pertama kali customer mengirim pesan ke sistem');

            $table->timestamp('last_received_at')
                ->comment('Terakhir kali pesan diterima dari nomor ini');

            $table->timestamps();

            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_whatsapp_confirmations');
    }
};
