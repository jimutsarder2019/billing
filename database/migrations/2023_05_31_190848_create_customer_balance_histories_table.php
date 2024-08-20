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
        Schema::create('customer_balance_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->refers('id')->on('customers');
            $table->foreignId('manager_id')->refers('id')->on('managers');
            $table->string('balance')->nullable();
            $table->string('update_Reasons')->nullable();
            $table->boolean('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_balance_histories');
    }
};
