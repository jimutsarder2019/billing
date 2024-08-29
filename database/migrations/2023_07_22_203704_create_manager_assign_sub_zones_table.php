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
        Schema::create('manager_assign_sub_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->refers('id')->on('managers');
            $table->foreignId('subzone_id')->refers('id')->on('sub_zones');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manager_assign_sub_zones');
    }
};
