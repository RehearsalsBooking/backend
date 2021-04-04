<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBandUserInvitesTable extends Migration
{
    public function up(): void
    {
        Schema::create('band_user_invites', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('band_id');
            $table->unsignedBigInteger('user_id');
            $table->string('role')->nullable();
            $table->timestamps();

            $table->foreign('band_id')->references('id')->on('bands')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
    }
}
