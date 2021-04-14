<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvitesTable extends Migration
{
    public function up(): void
    {
        Schema::create('invites', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('band_id');
            $table->string('email');
            $table->string('role')->nullable();
            $table->tinyInteger('status');
            $table->timestamps();

            $table->foreign('band_id')->references('id')->on('bands')->onDelete('cascade');
        });
    }

    public function down(): void
    {
    }
}
