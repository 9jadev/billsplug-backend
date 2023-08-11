<?php

use App\Models\MonifyTransaction;
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
        Schema::create('monify_transactions', function (Blueprint $table) {
            $table->id();
            $table->string("paymentReference");
            $table->string("customer_id");
            $table->string("amount")->nullable();
            $table->string("status")->default(MonifyTransaction::unverify);
            $table->longText("webhook_data")->nullable();
            $table->longText("verify_data")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monify_transactions');
    }
};
