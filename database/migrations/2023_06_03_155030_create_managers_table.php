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
        Schema::create('managers', function (Blueprint $table) {
            $table->id();
            $table->string('type')->comment('franchise, app_manager');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('password');
            $table->string('profile_photo_url')->nullable();
            $table->foreignId('zone_id')->nullable()->refers('id')->on('zones');
            $table->foreignId('sub_zone_id')->nullable()->refers('id')->on('sub_zones');
            $table->string('address')->nullable();
            $table->integer('grace_allowed')->nullable()->comment('value in days');
            $table->bigInteger('wallet')->default(0)->comment('customer collection');
            // $table->bigInteger('cash')->default(0)->comment('customer collection');
            // $table->bigInteger('bkash')->default(0)->comment('customer collection');
            // $table->bigInteger('bank_transfer')->default(0)->comment('customer collection');
            $table->float('panel_balance')->default(0)->comment('limit balance for add new customer');
            $table->boolean('prefix')->default(false);
            $table->string('prefix_text')->nullable();
            $table->foreignId('mikrotik_id')->nullable()->refers('id')->on('mikrotiks');
            $table->foreignId('package_id')->nullable()->refers('id')->on('packages')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('managers');
    }
};
