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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->refers('id')->on('customers')->nullable();
            $table->string('invoice_no');
            $table->unsignedBigInteger('package_id')->refers('id')->on('packages')->nullable();
            $table->BigInteger('customer_pkg_id_when_inv_payment')->nullable(); // customer current package id
            $table->unsignedBigInteger('manager_id')->refers('id')->on('managers')->nullable();
            $table->unsignedBigInteger('franchise_manager_id')->nullable()->refers('id')->on('managers')->nullable();
            $table->unsignedBigInteger('zone_id')->refers('id')->on('zones')->nullable();
            $table->unsignedBigInteger('sub_zone_id')->refers('id')->on('sub_zones')->nullable();
            $table->timestamp('expire_date')->nullable();
            $table->timestamp('customer_new_expire_date')->nullable();
            $table->timestamp('customer_old_expire_date')->nullable();
            $table->string('customer_status')->nullable();
            $table->decimal('amount', 8, 2);
            $table->decimal('received_amount', 8, 2)->nullable();
            $table->decimal('due_amount', 8, 2)->nullable();
            $table->decimal('advanced_amount', 8, 2)->nullable();
            $table->dateTime('last_payment_date')->nullable();
            $table->decimal('last_payment_amount', 8, 2)->nullable();
            $table->tinyInteger('notification_status')->default(0);
            $table->string('invoice_type')->nullable()->comment(INVOICE_TYPE_EXPENCE, INVOICE_TYPE_INCOME);
            $table->string('invoice_for')->nullable(); //new_user
            $table->string('manager_for')->nullable()->comment(APP_MANAGER, FRANCHISE_MANAGER);
            $table->string('paid_by')->nullable()->comment('cash, bkash');
            $table->string('transaction_id')->nullable();
            $table->string('status')->default('pending')->comment('pending,  due, paid, overpaid', 'rejected');
            $table->string('comment')->nullable()->comment('manually_created', 'new_user,auto_generated'); //,'Add manager panel Balance
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('received_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
