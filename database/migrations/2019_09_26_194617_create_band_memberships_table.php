<?php

use App\Models\Band;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBandMembershipsTable extends Migration
{
    public function up(): void
    {
        Schema::create('band_memberships', static function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Band::class, 'band_id')->constrained();
            $table->foreignIdFor(User::class, 'user_id')->constrained();
            $table->jsonb('roles')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['band_id', 'user_id']);
        });
    }
}
