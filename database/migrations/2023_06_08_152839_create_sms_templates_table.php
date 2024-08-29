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
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('sms_apis_id')->refers('id')->on('sms_apis')->nullable();
            $table->longText('template');
            $table->string('type')->default('sms')->comment('welcome_sms, invoice_create, invoice_payment, customer_account_create, account_expire, package_change, ticket_accept, ticket_pending, ticket_success, assign_ticket_to_support, user_info, update_balance');
            $table->boolean('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_templates');
    }
};
