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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('teacher_id')->nullable()->unique();
            $table->string('username')->unique();
            $table->integer('plan_id')->nullable();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->string('avatar')->nullable();
            $table->string('cover')->nullable();
            $table->string('wallet')->default('0.00');
            $table->uuid('invite_id')->unique()->nullable();


            $table->enum('status', ['active', 'deactive', 'block'])->default('active');
            $table->string('phone')->nullable()->unique();
            $table->timestamp('verified_at')->nullable();
            $table->string('code')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
