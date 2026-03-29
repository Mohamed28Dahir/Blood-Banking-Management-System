<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Schema;

class CreateBloodExpiryTracking extends Migration
{
    public function up()
    {
        Schema::create('blood_expiry_tracking', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('blood_bag_id');
            $table->date('expiry_date');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('blood_expiry_tracking');
    }
}