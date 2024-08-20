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
        Schema::create('daily_incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->refers('id')->on('managers');
            $table->string('service_name');
            $table->string('vouchar_no');
            $table->foreignId('category_id')->refers('id')->on('account_categories');
            $table->string('manager_for')->default(APP_MANAGER);
            $table->string('amount');
            $table->string('method');
            $table->string('transaction_id')->nullable();
            $table->string('date');
            $table->string('description');
            $table->string('status')->default('paid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_incomes');
    }
};
