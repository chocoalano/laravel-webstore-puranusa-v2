<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_outbound_logs', function (Blueprint $table) {
            $table->dropForeign('whatsapp_outbound_logs_broadcast_id_foreign');
            $table->dropIndex('whatsapp_outbound_logs_broadcast_id_index');
        });

        Schema::table('whatsapp_outbound_logs', function (Blueprint $table) {
            $table->string('broadcast_id', 100)
                ->nullable()
                ->comment('ID broadcast (lokal integer atau Qontak UUID string)')
                ->change();

            $table->index('broadcast_id');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_outbound_logs', function (Blueprint $table) {
            $table->dropIndex('whatsapp_outbound_logs_broadcast_id_index');
        });

        Schema::table('whatsapp_outbound_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('broadcast_id')
                ->nullable()
                ->comment('FK ke whatsapp_broadcasts lokal, nullable jika kirim manual/test')
                ->change();

            $table->foreign('broadcast_id')
                ->references('id')
                ->on('whatsapp_broadcasts')
                ->nullOnDelete();

            $table->index('broadcast_id');
        });
    }
};
