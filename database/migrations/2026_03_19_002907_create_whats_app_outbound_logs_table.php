<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_outbound_logs', function (Blueprint $table): void {
            $table->id();

            $table->unsignedBigInteger('broadcast_id')
                ->nullable()
                ->comment('FK ke whatsapp_broadcasts lokal, nullable jika kirim manual/test');

            $table->foreign('broadcast_id')
                ->references('id')
                ->on('whatsapp_broadcasts')
                ->nullOnDelete();

            $table->uuid('qontak_id')
                ->unique()
                ->comment('ID message broadcast dari Qontak API');

            $table->string('name')
                ->comment('Nama broadcast di Qontak');

            $table->uuid('organization_id')
                ->comment('Organization ID di Qontak');

            $table->uuid('channel_integration_id')
                ->comment('Channel integration ID di Qontak');

            $table->uuid('contact_list_id')
                ->nullable()
                ->comment('Contact list ID di Qontak, null jika kirim ke satu kontak');

            $table->uuid('contact_id')
                ->comment('Contact ID penerima di Qontak');

            $table->string('target_channel', 50)
                ->default('wa_cloud')
                ->comment('Channel target, contoh: wa_cloud');

            $table->timestamp('send_at')
                ->nullable()
                ->comment('Waktu pengiriman sesuai Qontak');

            $table->string('execute_status', 50)
                ->default('todo')
                ->comment('Status eksekusi Qontak: todo, done, failed, dll');

            $table->string('execute_type', 50)
                ->default('immediately')
                ->comment('Tipe eksekusi: immediately atau scheduled');

            $table->json('parameters')
                ->nullable()
                ->comment('Parameter template (header, body, buttons)');

            $table->json('message_status_count')
                ->nullable()
                ->comment('Jumlah status pesan: failed, delivered, read, pending, sent');

            $table->json('contact_extra')
                ->nullable()
                ->comment('Nilai variabel template yang dikirim, contoh: full_name, nominal');

            $table->json('message_template')
                ->nullable()
                ->comment('Snapshot data template Qontak saat pengiriman');

            $table->uuid('division_id')
                ->nullable()
                ->comment('Division ID di Qontak, opsional');

            $table->uuid('message_broadcast_plan_id')
                ->nullable()
                ->comment('Plan ID di Qontak jika bagian dari scheduled plan');

            $table->string('message_broadcast_error')
                ->nullable()
                ->comment('Pesan error dari Qontak, n/a jika tidak ada error');

            $table->string('sender_name')
                ->nullable()
                ->comment('Nama pengirim/operator di Qontak');

            $table->string('sender_email')
                ->nullable()
                ->comment('Email pengirim/operator di Qontak');

            $table->string('channel_account_name')
                ->nullable()
                ->comment('Nama akun channel WhatsApp, contoh: Puranusa');

            $table->string('channel_phone_number', 30)
                ->nullable()
                ->comment('Nomor telepon channel WhatsApp gateway');

            $table->timestamp('qontak_created_at')
                ->nullable()
                ->comment('Waktu record dibuat di Qontak');

            $table->timestamps();

            $table->index('broadcast_id');
            $table->index('execute_status');
            $table->index('qontak_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_outbound_logs');
    }
};
