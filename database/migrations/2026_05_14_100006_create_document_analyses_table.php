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
        Schema::create('document_analyses', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('document_id');
            $table->foreign('document_id')
                ->references('id')
                ->on('documents')
                ->cascadeOnDelete();

            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->cascadeOnDelete();

            $table->foreignId('requested_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Analysis classification
            $table->string('analysis_type', 50);

            // LLM traceability — which provider and model generated this
            $table->string('llm_provider', 50);
            $table->string('llm_model', 100);

            // Result
            $table->json('content')->comment('Structured analysis result');
            $table->decimal('confidence_score', 3, 2)->nullable()->comment('0.00 to 1.00');

            // Cost and performance tracking
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->unsignedInteger('processing_time_ms')->default(0);

            // Audit trail — what prompt/context was used
            $table->json('prompt_metadata')->nullable()->comment('Prompt template, RAG context refs, etc.');

            // Status
            $table->string('status', 30)->default('pending');

            // Versioning — allows re-analysis without losing history
            $table->unsignedInteger('version')->default(1);

            $table->timestamps();

            // Performance indexes
            $table->index(['document_id', 'analysis_type']);
            $table->index(['organization_id', 'created_at']);
            $table->index(['organization_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_analyses');
    }
};
