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
        Schema::create('account_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->foreignId('manager_id')->refers('id')->on('managers')->nullable();
            $table->string('manager_for')->default(APP_MANAGER);
            $table->boolean('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_categories');
    }
};
