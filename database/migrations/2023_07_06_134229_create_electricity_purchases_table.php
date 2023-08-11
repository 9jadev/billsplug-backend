<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('electricity_purchases', function (Blueprint $table) {
            $table->id();
            $table->string("amount");
            $table->string("provider");
            $table->string("token");
            $table->string('phone');
            $table->string('customer_id');
            $table->string('request_id');
            $table->longText('response');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('electricity_purchases');
    }
};
