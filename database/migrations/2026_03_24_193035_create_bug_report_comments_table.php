<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bug_report_comments', function (Blueprint $table): void {
            $table->id();

            $table->unsignedBigInteger('bug_report_id');

            $table->foreign('bug_report_id')
                ->references('id')
                ->on('bug_reports')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('user_id')
                ->nullable()
                ->comment('User admin yang membuat entri (null = dibuat otomatis sistem)');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->string('type', 30)
                ->default('comment')
                ->comment('Tipe entri: comment | internal_note | handling_step | status_change | assignment_change | category_change | resolution');

            $table->text('body')
                ->comment('Isi komentar, catatan, atau deskripsi langkah penanganan');

            // ─── Metadata perubahan (untuk entri otomatis) ───────────────────
            $table->string('old_value', 100)
                ->nullable()
                ->comment('Nilai lama sebelum perubahan (untuk status_change, assignment_change, category_change)');

            $table->string('new_value', 100)
                ->nullable()
                ->comment('Nilai baru setelah perubahan');

            // ─── Konteks langkah penanganan ──────────────────────────────────
            $table->unsignedSmallInteger('step_number')
                ->nullable()
                ->comment('Nomor urut langkah penanganan (untuk tipe handling_step)');

            $table->boolean('is_pinned')
                ->default(false)
                ->comment('Pin komentar penting agar tampil di bagian atas');

            $table->timestamps();

            $table->index('bug_report_id');
            $table->index('user_id');
            $table->index('type');
            $table->index(['bug_report_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bug_report_comments');
    }
};
