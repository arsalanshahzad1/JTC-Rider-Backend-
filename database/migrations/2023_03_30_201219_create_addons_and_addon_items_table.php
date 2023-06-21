<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addons_and_addon_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('addons_id')->nullable();
            $table->unsignedBigInteger('addon_items_id')->nullable();
            $table->timestamps();

            $table->foreign('addons_id')->references('id')->on('addons');
            $table->foreign('addon_items_id')->references('id')->on('addon_items');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addons_and_addon_items');
    }
};
