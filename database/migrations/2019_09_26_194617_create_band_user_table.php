<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBandUserTable extends Migration
{
    public function up(): void
    {
        Schema::create('band_user', static function (Blueprint $table) {
            $table->unsignedBigInteger('band_id');
            $table->unsignedBigInteger('user_id');
            $table->string('role')->nullable();
            $table->timestamps();

            $table->foreign('band_id')->references('id')->on('bands');
            $table->foreign('user_id')->references('id')->on('users');
            $table->unique(['band_id', 'user_id']);
        });
    }

    public function down(): void
    {
    }
}
