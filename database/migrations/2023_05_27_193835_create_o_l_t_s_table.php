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
        Schema::create('o_l_t_s', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('zone_id')->nullable()->refers('id')->on('zones');
            $table->foreignId('sub_zone_id')->nullable()->refers('id')->on('sub_zones');
            $table->string('mac')->nullable();
            $table->string('sys_running_time')->nullable();
            $table->string('sys_status')->nullable();
            $table->string('cpu_load')->nullable();
            $table->string('memory_load')->nullable();
            $table->string('sys_temperature')->nullable();
            $table->string('olt_ip')->nullable();
            $table->string('type')->nullable();
            $table->string('fatch_olt_type')->nullable();
            $table->string('non_of_pon_port')->nullable();
            $table->string('management_ip')->nullable();
            $table->string('management_vlan_ip')->nullable();
            $table->string('management_vlan_id')->nullable();
            $table->string('total_onu')->nullable();
            $table->string('status')->default(STATUS_TRUE);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('o_l_t_s');
    }
};
