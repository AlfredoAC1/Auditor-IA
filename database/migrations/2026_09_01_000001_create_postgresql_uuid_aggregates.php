<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('
            CREATE OR REPLACE FUNCTION min(uuid, uuid)
            RETURNS uuid AS $$
                SELECT CASE WHEN $1 IS NULL THEN $2 WHEN $2 IS NULL THEN $1 WHEN $1 < $2 THEN $1 ELSE $2 END;
            $$ LANGUAGE sql IMMUTABLE PARALLEL SAFE;
        ');

        DB::statement("
            DO $$
            BEGIN
                IF NOT EXISTS (
                    SELECT 1 FROM pg_aggregate a
                    JOIN pg_proc p ON a.aggfnoid = p.oid
                    WHERE p.proname = 'min' AND p.proargtypes = '2950'::oidvector
                ) THEN
                    CREATE AGGREGATE min(uuid) (
                        SFUNC = min,
                        STYPE = uuid,
                        COMBINEFUNC = min,
                        PARALLEL = SAFE,
                        SORTOP = <
                    );
                END IF;
            END $$;
        ");

        DB::statement('
            CREATE OR REPLACE FUNCTION max(uuid, uuid)
            RETURNS uuid AS $$
                SELECT CASE WHEN $1 IS NULL THEN $2 WHEN $2 IS NULL THEN $1 WHEN $1 > $2 THEN $1 ELSE $2 END;
            $$ LANGUAGE sql IMMUTABLE PARALLEL SAFE;
        ');

        DB::statement("
            DO $$
            BEGIN
                IF NOT EXISTS (
                    SELECT 1 FROM pg_aggregate a
                    JOIN pg_proc p ON a.aggfnoid = p.oid
                    WHERE p.proname = 'max' AND p.proargtypes = '2950'::oidvector
                ) THEN
                    CREATE AGGREGATE max(uuid) (
                        SFUNC = max,
                        STYPE = uuid,
                        COMBINEFUNC = max,
                        PARALLEL = SAFE,
                        SORTOP = >
                    );
                END IF;
            END $$;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP AGGREGATE IF EXISTS min(uuid)');
        DB::statement('DROP AGGREGATE IF EXISTS max(uuid)');
        DB::statement('DROP FUNCTION IF EXISTS min(uuid, uuid)');
        DB::statement('DROP FUNCTION IF EXISTS max(uuid, uuid)');
    }
};
