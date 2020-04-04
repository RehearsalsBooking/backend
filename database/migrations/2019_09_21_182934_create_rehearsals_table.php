<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRehearsalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('rehearsals', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('band_id')->nullable();
            $table->boolean('is_confirmed')->default(false);
            $table->decimal('price');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->foreign('band_id')->references('id')->on('bands');
        });

        DB::statement('
            ALTER TABLE rehearsals
            ADD COLUMN time tsrange NOT NULL;
        ');

        DB::statement('
            CREATE INDEX ON rehearsals USING GIST (time);
        ');

        DB::statement('
            CREATE EXTENSION IF NOT EXISTS btree_gist;
        ');

        DB::statement('
            ALTER TABLE rehearsals
            ADD EXCLUDE USING GIST (organization_id WITH =, time WITH &&);
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
