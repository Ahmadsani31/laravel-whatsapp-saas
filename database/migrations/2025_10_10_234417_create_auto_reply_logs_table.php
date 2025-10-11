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
        Schema::create('auto_reply_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auto_reply_id')->constrained()->onDelete('cascade');
            $table->foreignId('campaign_reply_id')->constrained()->onDelete('cascade');
            $table->string('phone_number');
            $table->text('sent_message');
            $table->timestamp('sent_at');
            $table->boolean('was_successful')->default(true);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['phone_number', 'auto_reply_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_reply_logs');
    }
};
