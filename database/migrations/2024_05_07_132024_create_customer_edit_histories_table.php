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
        Schema::create('customer_edit_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->reference('id')->on('customers')->onDelete('cascade');
            $table->foreignId('manager_id')->reference('id')->on('managers');
            $table->string('subject')->comment('created, updated, additional');
            $table->longText('note');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_edit_histories');
    }
};
