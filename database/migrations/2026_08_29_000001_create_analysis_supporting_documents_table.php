<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the pivot table that links a DocumentAnalysis to one or more
     * supporting Document records. This preserves context so the AI can
     * reference the original document, its prior analysis, AND all
     * subsequently attached supporting documents in a single enriched prompt.
     */
    public function up(): void
    {
        Schema::create('analysis_supporting_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // The analysis that owns this supporting-document relationship
            $table->uuid('analysis_id');
            $table->foreign('analysis_id')
                ->references('id')
                ->on('document_analyses')
                ->cascadeOnDelete();

            // The document being attached as supporting context
            $table->uuid('document_id');
            $table->foreign('document_id')
                ->references('id')
                ->on('documents')
                ->cascadeOnDelete();

            // Tenant safety — always store organization_id for scoped queries
            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->cascadeOnDelete();

            // Who attached this document
            $table->foreignId('attached_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Semantic relationship descriptor
            $table->string('relationship_type', 50)
                ->nullable()
                ->comment('e.g. referenced, related, clarification, complementary');

            // Optional user-provided note explaining why this doc is relevant
            $table->text('notes')->nullable();

            $table->timestamps();

            // Prevent the same document from being attached twice to the same analysis
            $table->unique(['analysis_id', 'document_id'], 'unique_analysis_document');

            // Performance indexes
            $table->index('analysis_id');
            $table->index('document_id');
            $table->index(['organization_id', 'analysis_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_supporting_documents');
    }
};
