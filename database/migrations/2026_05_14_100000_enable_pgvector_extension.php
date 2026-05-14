<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Enable the pgvector extension in PostgreSQL.
 *
 * This MUST run before any migration that uses the vector column type.
 * Requires pgvector to be installed on the PostgreSQL server.
 *
 * Installation: https://github.com/pgvector/pgvector
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS vector');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP EXTENSION IF EXISTS vector');
    }
};
