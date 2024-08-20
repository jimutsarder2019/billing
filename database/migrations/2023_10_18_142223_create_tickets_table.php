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
        if (Schema::hasTable('tickets')) return;
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('ticket_no');
            $table->foreignId('ticket_category_id')->constrained();
            $table->foreignId('customer_id')->references('id')->on('customers');
            $table->foreignId('manager_id')->constrained()->comment('who is created this ticket');
            $table->string('priority');
            $table->boolean('send_sms')->nullable();
            $table->longText('note')->nullable();
            $table->string('status')->default(TICKET_PENDING)->comment('panding,processing,completed');
            $table->bigInteger('solved_by')->nullable();
            $table->bigInteger('assign_to')->nullable();
            $table->bigInteger('create_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
