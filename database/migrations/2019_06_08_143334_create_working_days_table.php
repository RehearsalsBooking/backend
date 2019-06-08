<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkingDaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('working_days', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('organization_id')->references('id')->on('organizations');
            $table->tinyInteger('day');
            $table->string('opens_at')->nullable();
            $table->string('closes_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('working_days');
    }
}
