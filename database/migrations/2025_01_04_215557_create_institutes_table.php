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
        Schema::create('institutes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('plan_id')->nullable();
            $table->string('phone')->nullable()->unique();
            $table->string('website')->nullable();
            $table->string('avatar')->nullable();
            $table->string('cover')->nullable();
            $table->string('wallet')->default('0.00');
            $table->string('card')->nullable();
            $table->string('profit_share')->default('50');
            $table->uuid('invite_id')->unique()->nullable();
            $table->enum('status', ['active', 'deactive', 'block'])->default('deactive');
            $table->text('description')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('code')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institutes');
    }
};
