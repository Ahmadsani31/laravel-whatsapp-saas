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
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Key name for identification
            $table->string('key', 64)->unique(); // The API key itself
            $table->text('permissions')->nullable(); // Permissions (JSON)
            $table->boolean('is_active')->default(true); // Active status
            $table->timestamp('last_used_at')->nullable(); // Last usage timestamp
            $table->timestamp('expires_at')->nullable(); // Expiration date
            $table->timestamps();
            
            $table->index(['key', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
