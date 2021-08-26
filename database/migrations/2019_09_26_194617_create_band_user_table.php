<?php

use App\Models\Band;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBandUserTable extends Migration
{
    public function up(): void
    {
        Schema::create('band_members', static function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Band::class, 'band_id')->constrained();
            $table->foreignIdFor(User::class, 'user_id')->constrained();
            $table->string('role')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
    }
}
