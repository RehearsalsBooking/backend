<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationEquipmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('organization_equipment', static function (Blueprint $table) {
            $table->id();
            $table->string('item_description');
            $table->string('model')->nullable();
            $table->string('photo')->nullable();
            $table->unsignedInteger('organization_id');
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations');
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
