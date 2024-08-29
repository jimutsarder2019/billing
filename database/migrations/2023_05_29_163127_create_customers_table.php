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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('id_in_mkt')->nullable();
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('gender')->nullable();
            $table->string('national_id')->nullable();
            $table->string('phone')->nullable();
            $table->string('date_of_birth')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('address')->nullable();
            $table->foreignId('zone_id')->refers('id')->on('zones')->nullable();
            $table->foreignId('sub_zone_id')->nullable()->refers('id')->on('sub_zones');
            $table->string('registration_date')->nullable();
            $table->boolean('is_schedule_package_change')->default(STATUS_FALSE);
            $table->bigInteger('schedule_package_id')->nullable();
            $table->double('schedule_package_bill')->nullable();
            $table->integer('allow_grace')->nullable()->comment('allow grace if null');
            $table->string('connection_date')->nullable();
            $table->timestamp('expire_date')->nullable()->comment();
            $table->foreignId('package_id')->refers('id')->on('packages')->nullable();
            $table->foreignId('mikrotik_id')->refers('id')->on('mikrotiks')->nullable();
            $table->foreignId('manager_id')->refers('id')->on('managers')->nullable();
            $table->bigInteger('purchase_package_id')->nullable();
            $table->string('bill')->nullable();
            $table->float('franchise_package_price')->nullable()->comment('franchise can add custom package price for his user');
            $table->float('discount')->nullable();
            $table->float('wallet')->default(00);
            $table->string('service')->default('PPPoE');
            $table->string('username')->nullable();
            $table->string('mac_address')->nullable();
            $table->boolean('is_send_sms')->default(STATUS_TRUE);
            $table->string('password')->nullable();
            $table->string('customer_for')->default(APP_MANAGER)->comment(APP_MANAGER, FRANCHISE_MANAGER);
            $table->string('avater')->nullable();
            $table->string('additional_phone')->nullable();
            $table->boolean('is_sms_sent_before_expire')->default(0)->comment('0=false');
            $table->boolean('is_auto_invoice_create')->default(STATUS_TRUE)->comment('change this table is create auto invoice');
            $table->string('status')->default(CUSTOMER_PENDING)->comment('active,pending,approved,suspende,new_register,expire, delete');
            $table->boolean('mikrotik_disabled')->default(STATUS_FALSE)->comment('true = enabled, false = desabled');
            $table->timestamps();
        });
    }
    /**
     * 
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
