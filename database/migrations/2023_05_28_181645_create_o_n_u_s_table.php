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
        Schema::create('o_n_u_s', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mac');
            $table->foreignId('olt_id')->refers('id')->on('o_l_t_s');
            $table->string('pon_port');
            $table->string('onu_id');
            $table->string('rx_power');
            $table->string('distance');
            $table->boolean('vlan_tagged')->default(STATUS_FALSE);
            $table->bigInteger('vlan_id')->nullable();
            $table->foreignId('customer_id')->refers('id')->on('customers')->nullable();
            $table->foreignId('zone_id')->refers('id')->on('zones')->nullable();
            $table->foreignId('sub_zone_id')->refers('id')->on('sub_zones')->nullable();
            $table->boolean('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('o_n_u_s');
    }
};
