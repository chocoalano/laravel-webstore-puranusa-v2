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
        Schema::table('contents_category', function (Blueprint $table): void {
            $table->string('icon_key', 100)->nullable()->after('slug');
            $table->string('accent_hex', 7)->nullable()->after('icon_key');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('accent_hex');
            $table->string('thumbnail_url')->nullable()->after('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contents_category', function (Blueprint $table): void {
            $table->dropColumn(['icon_key', 'accent_hex', 'sort_order', 'thumbnail_url']);
        });
    }
};
