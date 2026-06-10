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
        Schema::create('law_embeddings', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Law identification
            $table->string('law_identifier', 100)->comment('e.g. "Código Civil Art. 1545"');
            $table->string('law_name', 500)->comment('e.g. "Código Civil de Chile"');
            $table->string('article_number', 50)->nullable();
            $table->string('book_or_title', 255)->nullable()->comment('Book/Title/Chapter within the law');

            // Content
            $table->text('content');
            $table->string('content_hash', 64)->comment('SHA-256 to detect changes on re-ingestion');
            $table->unsignedInteger('token_count')->default(0);

            // Versioning — allows re-ingestion without losing history
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_current')->default(true);

            $table->json('metadata')->nullable();
            $table->timestamps();

            // Lookup indexes
            $table->index('law_identifier');
            $table->index(['law_name', 'article_number']);
            $table->index('content_hash');
            $table->index('is_current');
        });

        // Add vector column via raw SQL (pgvector)
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE law_embeddings ADD COLUMN embedding vector(1536)');
            DB::statement('CREATE INDEX law_embeddings_embedding_idx ON law_embeddings USING hnsw (embedding vector_cosine_ops)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('law_embeddings');
    }
};
