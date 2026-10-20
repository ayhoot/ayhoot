<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('cover')->nullable();
            $table->enum('monetize', ['free', 'paid'])->default('free');
            $table->string('price')->nullable();
            $table->tinyInteger('is_verified')->default(1);
            $table->enum('status', ['published', 'pending', 'rejected'])->default('pending');
            $table->unsignedBigInteger('institute_id')->nullable();
            $table->foreign('institute_id')->references('id')->on('institutes')->onDelete('cascade');
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
            $table->text('rejected_reason')->nullable();
            $table->enum('purchased', ['yes', 'no'])->default('no');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->json('options')->nullable();
            $table->string('archive')->nullable();
 $table->string('locale')->default('en');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
