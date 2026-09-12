<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('document_chunks', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('document_id');
            $table->foreign('document_id')
                ->references('id')
                ->on('documents')
                ->cascadeOnDelete();

            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('chunk_index');
            $table->text('content');
            $table->unsignedInteger('token_count')->default(0);

            // Chunk context (for reassembly and reference)
            $table->string('section_title', 500)->nullable();
            $table->unsignedInteger('start_page')->nullable();
            $table->unsignedInteger('end_page')->nullable();

            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Performance indexes
            $table->index(['document_id', 'chunk_index']);
            $table->index('organization_id');
        });

        // Add vector column via raw SQL (pgvector) if available, otherwise fallback to json
        $hasVector = false;
        if (DB::connection()->getDriverName() === 'pgsql') {
            try {
                $hasVector = DB::selectOne("SELECT 1 FROM pg_extension WHERE extname = 'vector'") !== null;
            } catch (Exception $e) {
            }
        }

        if ($hasVector) {
            DB::statement('ALTER TABLE document_chunks ADD COLUMN embedding vector(1536)');
            DB::statement('CREATE INDEX document_chunks_embedding_idx ON document_chunks USING hnsw (embedding vector_cosine_ops)');
        } else {
            Schema::table('document_chunks', function (Blueprint $table) {
                $table->json('embedding')->nullable()->comment('Fallback for pgvector when not available');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_chunks');
    }
};
