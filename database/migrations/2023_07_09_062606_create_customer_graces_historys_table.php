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
        Schema::create('customer_grace_historys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->refers('id')->on('customers')->onDelete('cascade');
            $table->foreignId('manager_id')->nullable()->refers('id')->on('managers');
            $table->integer('grace');
            $table->string('customer_new_expire_date')->nullable();
            $table->string('grace_before_expire_date')->nullable();
            $table->boolean('status')->default(STATUS_TRUE);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_grace_historys');
    }
};
