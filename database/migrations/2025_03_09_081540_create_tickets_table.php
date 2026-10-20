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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable(); 
            $table->text('message'); 
            $table->enum('status', ['open', 'answered', 'closed'])->default('open');
            $table->foreignId('parent_id')->nullable()->constrained('tickets')->onDelete('cascade');
            $table->string('sender_type'); 
            $table->unsignedBigInteger('sender_id'); 
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
