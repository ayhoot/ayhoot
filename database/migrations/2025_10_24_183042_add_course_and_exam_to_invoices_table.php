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
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'exam_id')) {
                $table->unsignedBigInteger('exam_id')->nullable()->after('plan_id');
                $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
            }
            
            if (!Schema::hasColumn('invoices', 'course_id')) {
                $table->unsignedBigInteger('course_id')->nullable()->after('exam_id');
                $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            }
            
            if (!Schema::hasColumn('invoices', 'student_id')) {
                $table->unsignedBigInteger('student_id')->nullable()->after('institute_id');
                $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            }
            
            if (!Schema::hasColumn('invoices', 'meta')) {
                $table->json('meta')->nullable()->after('currency');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'exam_id')) {
                $table->dropForeign(['exam_id']);
                $table->dropColumn('exam_id');
            }
            
            if (Schema::hasColumn('invoices', 'course_id')) {
                $table->dropForeign(['course_id']);
                $table->dropColumn('course_id');
            }
            
            if (Schema::hasColumn('invoices', 'student_id')) {
                $table->dropForeign(['student_id']);
                $table->dropColumn('student_id');
            }
            
            if (Schema::hasColumn('invoices', 'meta')) {
                $table->dropColumn('meta');
            }
        });
    }
};