<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->string('gateway');
            $table->string('transaction_id')->unique()->comment('Gateway-specific transaction ID, e.g., Zarinpal Authority');
            $table->decimal('amount', 15, 0);
            $table->enum('status', ['pending', 'successful', 'failed', 'cancelled'])->default('pending');
            $table->text('meta')->nullable()->comment('To store the full gateway response');
            $table->timestamp('paid_at')->nullable();
            $table->string('ref_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
