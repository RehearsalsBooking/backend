<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('organization_prices', static function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('day');
            $table->decimal('price');
            $table->integer('organization_id');
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations');
        });

        DB::statement(
            "
        CREATE OR REPLACE FUNCTION time_subtype_diff(x time, y time) RETURNS float8 AS
        'SELECT EXTRACT(EPOCH FROM (x - y))' LANGUAGE sql STRICT IMMUTABLE;
"
        );
        DB::statement("
        DO $$
        BEGIN
            IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'timerange') THEN
                CREATE TYPE timerange AS RANGE (
                    subtype = time,
                    subtype_diff = time_subtype_diff
                );
            END IF;
        END$$;
        ");

        DB::statement(
            '
        ALTER TABLE organization_prices
        ADD COLUMN time timerange NOT NULL
        '
        );

        DB::statement('
            CREATE EXTENSION IF NOT EXISTS btree_gist;
        ');

        DB::statement('
            ALTER TABLE organization_prices
            ADD EXCLUDE USING GIST (organization_id WITH =, day WITH =, time WITH &&);
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }
}
