<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bug_reports', function (Blueprint $table): void {
            $table->id();

            // ─── Identitas pelapor ───────────────────────────────────────────
            $table->string('reporter_type', 20)
                ->comment('Tipe pelapor: customer | user | anonymous');

            $table->unsignedBigInteger('reporter_id')
                ->nullable()
                ->comment('ID customer atau user (null jika anonymous)');

            $table->string('reporter_name', 100)
                ->nullable()
                ->comment('Nama pelapor (diisi jika anonymous)');

            $table->string('reporter_email', 150)
                ->nullable()
                ->comment('Email pelapor (diisi jika anonymous)');

            // ─── Detail bug ──────────────────────────────────────────────────
            $table->string('title')
                ->comment('Judul singkat bug');

            $table->text('description')
                ->comment('Deskripsi lengkap bug yang dialami');

            $table->text('steps_to_reproduce')
                ->nullable()
                ->comment('Langkah-langkah untuk mereproduksi bug');

            $table->text('expected_behavior')
                ->nullable()
                ->comment('Perilaku yang seharusnya terjadi');

            $table->text('actual_behavior')
                ->nullable()
                ->comment('Perilaku yang sebenarnya terjadi');

            // ─── Konteks platform ────────────────────────────────────────────
            $table->string('platform', 20)
                ->comment('Platform: web | mobile');

            $table->string('source', 30)
                ->comment('Sumber aplikasi: storefront | admin_console');

            $table->string('web_screen', 20)
                ->nullable()
                ->comment('Ukuran layar web: desktop | tablet | smartphone');

            $table->string('mobile_type', 20)
                ->nullable()
                ->comment('Tipe mobile OS: android | ios');

            $table->string('page_url', 500)
                ->nullable()
                ->comment('URL halaman tempat bug ditemukan');

            // ─── Informasi environment ───────────────────────────────────────
            $table->string('browser', 80)
                ->nullable()
                ->comment('Nama browser (misal: Chrome, Firefox, Safari)');

            $table->string('browser_version', 30)
                ->nullable()
                ->comment('Versi browser');

            $table->string('os', 80)
                ->nullable()
                ->comment('Sistem operasi (misal: Windows 11, macOS 14, Android 14)');

            $table->string('os_version', 30)
                ->nullable()
                ->comment('Versi sistem operasi');

            $table->string('device_model', 100)
                ->nullable()
                ->comment('Model perangkat (khusus mobile, misal: Samsung Galaxy S24)');

            $table->string('app_version', 30)
                ->nullable()
                ->comment('Versi aplikasi saat bug terjadi');

            $table->string('screen_resolution', 20)
                ->nullable()
                ->comment('Resolusi layar (misal: 1920x1080)');

            // ─── Triase & status ─────────────────────────────────────────────
            $table->string('error_category', 30)
                ->nullable()
                ->comment('Kategori akar masalah: human_error | system_error | ui_ux_error | performance_issue | data_error | security_issue | configuration_error | unknown');

            $table->string('severity', 20)
                ->default('medium')
                ->comment('Keparahan: critical | high | medium | low');

            $table->string('priority', 20)
                ->default('medium')
                ->comment('Prioritas: urgent | high | medium | low');

            $table->string('status', 20)
                ->default('open')
                ->comment('Status: open | under_review | confirmed | in_progress | resolved | closed | rejected | duplicate');

            $table->unsignedBigInteger('duplicate_of_id')
                ->nullable()
                ->comment('ID bug_report yang ini merupakan duplikatnya');

            $table->foreign('duplicate_of_id')
                ->references('id')
                ->on('bug_reports')
                ->nullOnDelete();

            // ─── Penugasan & resolusi ────────────────────────────────────────
            $table->unsignedBigInteger('assigned_to')
                ->nullable()
                ->comment('ID user (admin/developer) yang ditugaskan menangani');

            $table->foreign('assigned_to')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->text('resolution_note')
                ->nullable()
                ->comment('Catatan resolusi atau alasan penolakan');

            $table->timestamp('resolved_at')
                ->nullable()
                ->comment('Waktu bug selesai diperbaiki');

            $table->timestamp('closed_at')
                ->nullable()
                ->comment('Waktu laporan ditutup');

            $table->timestamps();

            // ─── Indexes ─────────────────────────────────────────────────────
            $table->index('reporter_type');
            $table->index(['reporter_type', 'reporter_id']);
            $table->index('platform');
            $table->index('source');
            $table->index('status');
            $table->index('severity');
            $table->index('priority');
            $table->index('assigned_to');
            $table->index('error_category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bug_reports');
    }
};
