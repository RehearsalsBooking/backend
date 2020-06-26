<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('organizations', static function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address');
            // for now simple string is okay
            // because i wont be querying this column
            // it's only needed for representation
            $table->string('coordinates')->nullable();
            $table->boolean('is_active')->default(false);
            $table->text('gear')->nullable();
            $table->unsignedInteger('owner_id');
            $table->string('avatar')->nullable();
            $table->timestamps();

            $table->foreign('owner_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
}
