<?php

use App\Models\Band;
use App\Models\BandGenre;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBandsGenres extends Migration
{
    public function up(): void
    {
        Schema::create('bands_genres', function (Blueprint $table) {
            $table->foreignIdFor(Band::class, 'band_id');
            $table->foreignIdFor(BandGenre::class, 'genre_id');
            $table->timestamps();

            $table->unique(['band_id', 'genre_id']);
        });
    }

    public function down(): void
    {
    }
}
