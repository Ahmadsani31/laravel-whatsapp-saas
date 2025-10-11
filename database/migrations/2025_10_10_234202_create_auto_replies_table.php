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
        Schema::create('auto_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('trigger_keywords')->nullable(); // Keywords that trigger this reply
            $table->text('reply_message'); // Auto reply message
            $table->boolean('is_active')->default(true);
            $table->integer('delay_seconds')->default(0); // Delay before sending reply
            $table->boolean('send_once_per_contact')->default(true); // Send only once per contact
            $table->timestamps();

            $table->index(['campaign_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_replies');
    }
};
