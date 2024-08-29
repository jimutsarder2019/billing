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
        if (Schema::hasTable('invoice_edit_histories')) return;
        Schema::create('invoice_edit_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->nullable()->reference('id')->on('manager_id');
            $table->foreignId('invoice_id')->reference('id')->on('invoices')->onDelete('cascade');
            $table->string('invoice_amount');
            $table->string('previous_received_amount')->nullable();
            $table->string('new_received_amount')->nullable();
            $table->string('total_received_amount')->nullable();
            $table->string('paid_by')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_edit_histories');
    }
};
