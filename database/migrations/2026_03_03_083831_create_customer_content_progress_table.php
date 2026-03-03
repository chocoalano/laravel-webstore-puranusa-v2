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
        Schema::create('customer_content_progress', function (Blueprint $table): void {
            $table->id();
            // customers.id adalah INT UNSIGNED (bukan BIGINT), gunakan unsignedInteger
            $table->unsignedInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->foreignId('content_category_id')->constrained('contents_category')->cascadeOnDelete();
            $table->foreignId('content_id')->nullable()->constrained('contents')->nullOnDelete();
            $table->decimal('progress', 5, 4)->default(0); // 0.0000 to 1.0000
            $table->unsignedInteger('position_sec')->default(0);
            $table->timestamp('last_watched_at')->nullable();
            $table->timestamps();

            $table->unique(['customer_id', 'content_category_id']);
            $table->index(['customer_id', 'last_watched_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_content_progress');
    }
};
