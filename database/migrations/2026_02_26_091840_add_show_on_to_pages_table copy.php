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
        if (! Schema::hasColumn('pages', 'show_on')) {
            Schema::table('pages', function (Blueprint $table): void {
                $table->enum('show_on', [
                    'header_top_bar',
                    'header_navbar',
                    'header_bottombar',
                    'footer_main',
                    'bottom_main',
                ])->nullable()->default('bottom_main')->after('template');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('pages', 'show_on')) {
            Schema::table('pages', function (Blueprint $table): void {
                $table->dropColumn('show_on');
            });
        }
    }
};
