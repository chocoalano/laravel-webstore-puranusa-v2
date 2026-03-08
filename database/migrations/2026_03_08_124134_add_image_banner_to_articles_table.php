<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('articles')) {
            return;
        }

        if (! Schema::hasColumn('articles', 'image_banner')) {
            Schema::table('articles', function (Blueprint $table): void {
                $table->string('image_banner')->nullable()->after('seo_description');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('articles')) {
            return;
        }

        if (Schema::hasColumn('articles', 'image_banner')) {
            Schema::table('articles', function (Blueprint $table): void {
                $table->dropColumn('image_banner');
            });
        }
    }
};
