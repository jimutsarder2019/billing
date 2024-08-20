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
        Schema::create('manager_balance_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->nullable()->refers('id')->on('managers');
            $table->foreignId('app_manager_id')->nullable()->refers('id')->on('managers');
            $table->foreignId('invoice_id')->nullable()->refers('id')->on('invoices');
            $table->float('balance');
            $table->string('history_for')->default('update_balance')->comment('add balance,add new user');
            $table->string('sign')->comment('+ -');
            $table->string('note')->nullable();
            $table->float('franchise_panel_balance')->nullable()->comment('franchise panel balance or app manager main balance');
            $table->string('status')->default(STATUS_PENDING)->comment(STATUS_PENDING, STATUS_ACCEPTED, STATUS_REJECTED);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manager_balance_histories');
    }
};
