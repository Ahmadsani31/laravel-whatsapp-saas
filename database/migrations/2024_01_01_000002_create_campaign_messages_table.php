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
        Schema::create('campaign_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->string('phone_number');
            $table->text('message_content');
            $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->string('whatsapp_message_id')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'status']);
            $table->index('phone_number');
            $table->index('whatsapp_message_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_messages');
    }
};