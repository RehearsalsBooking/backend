<?php

use App\Models\Band;
use App\Models\Organization\OrganizationRoom;
use App\Models\User;
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
            $table->foreignIdFor(OrganizationRoom::class, 'organization_room_id')->constrained('organization_rooms');
            $table->foreignIdFor(User::class, 'user_id')->constrained();
            $table->foreignIdFor(Band::class, 'band_id')->nullable()->constrained();
            $table->boolean('is_paid')->default(false);
            $table->decimal('price');
            $table->timestampRange('time');
            $table->timestamps();

            $table->excludeRangeOverlapping('time', 'organization_room_id');
            $table->spatialIndex('time');
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
