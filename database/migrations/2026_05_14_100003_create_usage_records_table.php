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
        Schema::create('usage_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('usage_type', 50);
            $table->unsignedInteger('quantity')->default(1);

            // Contextual metadata (document_id, tokens_used, provider, model, etc.)
            $table->json('metadata')->nullable();

            // Billing period this usage belongs to
            $table->date('period_start');
            $table->date('period_end');

            $table->timestamp('created_at')->useCurrent();

            // Fast lookups: "how many uploads this org did in this period?"
            $table->index(['organization_id', 'usage_type', 'period_start', 'period_end'], 'usage_org_type_period');
            $table->index(['organization_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usage_records');
    }
};
