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
        Schema::create('payment_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('authority', 36)->unique();
            $table->unsignedBigInteger('user_id');
            $table->string('user_type');
            $table->string('content_type');
            $table->unsignedBigInteger('content_id');
            $table->decimal('amount', 10, 2);
            $table->decimal('original_amount', 10, 2);
            $table->string('discount_code')->nullable();
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->string('payment_reference')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
            
            $table->index(['authority', 'status']);
            $table->index(['user_id', 'user_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_sessions');
    }
};
