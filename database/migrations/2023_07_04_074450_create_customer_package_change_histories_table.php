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
        Schema::create('customer_package_change_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->refers('id')->on('customers')->nullable();
            $table->foreignId('package_id')->refers('id')->on('packages')->nullable();
            $table->foreignId('manager_id')->refers('id')->on('managers')->nullable();
            $table->timestamp('expire_date')->nullable();
            $table->boolean('status')->default(STATUS_TRUE);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_package_change_histories');
    }
};
