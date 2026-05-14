<?php

declare(strict_types=1);

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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('slug', 50)->unique();
            $table->text('description')->nullable();

            // Stripe integration
            $table->string('stripe_price_id', 255)->nullable()->unique();

            // Usage limits (NULL = unlimited)
            $table->unsignedInteger('max_documents_per_month')->nullable();
            $table->unsignedInteger('max_analyses_per_month')->nullable();
            $table->unsignedBigInteger('max_storage_bytes')->nullable();
            $table->unsignedInteger('max_tokens_per_month')->nullable();

            // Plan configuration
            $table->json('features')->nullable()->comment('Feature flags and extra config for this plan');
            $table->unsignedInteger('price_clp')->default(0)->comment('Monthly price in CLP');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_free')->default(false);

            $table->timestamps();

            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
