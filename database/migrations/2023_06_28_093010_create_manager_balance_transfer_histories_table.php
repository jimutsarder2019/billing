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
        Schema::create('manager_balance_transfer_histories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sender_id');
            $table->bigInteger('reciver_id');
            $table->bigInteger('amount');
            $table->bigInteger('recived_amount')->nullable()->comment('store amount when other manager will received balance');
            $table->string('status')->default('pending')->comment('pending, accepted, rejected, custom_accepted');
            $table->boolean('notification_status')->default(STATUS_FALSE);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manager_balance_transfer_histories');
    }
};
