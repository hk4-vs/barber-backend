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
        Schema::create('sheet_booking_models', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('shop_id')->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->string('service_id')->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->date('date')->format('d-m-Y');
            $table->time('time');
            $table->string('user_name');
            $table->enum('order', ["processing", "confirmed", "cancel by user", "cancel by shop"])->default("processing");


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sheet_booking_models');
    }
};
