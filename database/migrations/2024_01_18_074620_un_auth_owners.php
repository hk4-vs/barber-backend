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
        Schema::create('un_auth_owners', function (Blueprint $table) {
            $table->id();
            $table->string('otp');
            $table->string('name');
            $table->string('email');
            $table->boolean('verified')->default(false);
            $table->string('password');
            $table->string('phone', 11);
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('address');
            $table->string('owner_photo');
            $table->string('shop_name');
            $table->string('shop_state');
            $table->string('shop_city');
            $table->string('pincode');
            $table->string('shop_address');
            $table->string('shop_photo');
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
        Schema::dropIfExists('un_auth_owners');
    }
};
