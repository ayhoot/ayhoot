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
        Schema::create('exam_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exam_id');
            $table->unsignedBigInteger('purchaser_id');
            $table->string('purchaser_type'); // 'teacher', 'institute', 'student'
            $table->unsignedBigInteger('invoice_id')->nullable(); // Make nullable for now
            $table->decimal('amount_paid', 10, 2);
            $table->decimal('original_amount', 10, 2);
            $table->string('discount_code')->nullable();
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('payment_method')->default('zarinpal');
            $table->string('payment_status')->default('completed');
            $table->string('payment_reference')->nullable();
            $table->timestamp('purchased_at');
            $table->timestamps();

            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
            // Make invoice foreign key nullable for now
            // $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            
            // Ensure unique purchase per user per exam
            $table->unique(['exam_id', 'purchaser_id', 'purchaser_type'], 'unique_exam_purchase');
            
            $table->index(['purchaser_id', 'purchaser_type']);
            $table->index('exam_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_purchases');
    }
};