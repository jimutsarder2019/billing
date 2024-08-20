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
        Schema::create('sentmessages', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->string('message_id')->nullable();
            $table->foreignId('sms_apis_id')->nullable()->constrained();
            $table->foreignId('sms_templates_id')->nullable()->constrained();
            $table->foreignId('users_id')->nullable()->constrained();
            $table->longText('message')->nullable();
            $table->string('status')->nullable();
            $table->string('status_code')->nullable();
            $table->string('template_type')->nullable()->comment();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sentmessages');
    }
};
