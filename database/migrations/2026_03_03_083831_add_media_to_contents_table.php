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
        Schema::table('contents', function (Blueprint $table): void {
            $table->string('content_type', 50)->nullable()->after('vlink');
            $table->string('thumbnail_url')->nullable()->after('content_type');
            $table->unsignedInteger('duration_sec')->nullable()->after('thumbnail_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table): void {
            $table->dropColumn(['content_type', 'thumbnail_url', 'duration_sec']);
        });
    }
};
