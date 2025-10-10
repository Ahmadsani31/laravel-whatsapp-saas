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
        Schema::create('campaign_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('campaign_message_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('phone_number');
            $table->text('message_content');
            $table->string('whatsapp_message_id')->nullable();
            $table->timestamp('received_at');
            $table->boolean('is_processed')->default(false);
            $table->timestamps();

            $table->index(['campaign_id', 'received_at']);
            $table->index(['phone_number', 'received_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_replies');
    }
};
