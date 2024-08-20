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
        Schema::create('manager_assign_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->nullable()->refers('id')->on('managers');
            $table->foreignId('package_id')->nullable()->refers('id')->on('packages');
            $table->boolean('is_manager_can_add_custom_package_price')->default(STATUS_FALSE);
            $table->float('manager_custom_price')->nullable();
            $table->foreignId('status')->default(STATUS_TRUE);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manager_assign_packages');
    }
};
