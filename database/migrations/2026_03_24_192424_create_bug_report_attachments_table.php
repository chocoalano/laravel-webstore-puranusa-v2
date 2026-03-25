<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bug_report_attachments', function (Blueprint $table): void {
            $table->id();

            $table->unsignedBigInteger('bug_report_id');

            $table->foreign('bug_report_id')
                ->references('id')
                ->on('bug_reports')
                ->cascadeOnDelete();

            $table->string('file_path')
                ->comment('Path file di storage');

            $table->string('file_name', 255)
                ->comment('Nama asli file yang diupload');

            $table->string('mime_type', 80)
                ->nullable()
                ->comment('MIME type file (misal: image/png, image/jpeg)');

            $table->unsignedInteger('file_size')
                ->nullable()
                ->comment('Ukuran file dalam bytes');

            $table->string('caption', 255)
                ->nullable()
                ->comment('Keterangan singkat tentang screenshot/file');

            $table->timestamps();

            $table->index('bug_report_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bug_report_attachments');
    }
};
