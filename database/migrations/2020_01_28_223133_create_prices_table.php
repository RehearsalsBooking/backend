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
            $table->timeRange('time');
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->excludeRangeOverlapping('time', 'organization_id', 'day');
        });
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
