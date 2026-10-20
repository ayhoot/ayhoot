<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Exam;
use App\Models\Course;
use App\Models\Teacher;
use App\Models\Institute;
use App\Models\Student;
use App\Models\Invoice;
use App\Models\ExamPurchase;
use App\Models\CoursePurchase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_access_for_owner()
    {
        $teacher = Teacher::factory()->create();
        $exam = Exam::factory()->create(['teacher_id' => $teacher->id, 'monetize' => 'paid']);

        $this->assertTrue($exam->hasAccessFor($teacher));
        $this->assertFalse($exam->isPurchasedBy($teacher->id, 'teacher')); // Owner doesn't need to purchase
    }

    public function test_free_exam_access_for_all()
    {
        $teacher = Teacher::factory()->create();
        $otherTeacher = Teacher::factory()->create();
        $exam = Exam::factory()->create(['teacher_id' => $teacher->id, 'monetize' => 'free']);

        $this->assertTrue($exam->hasAccessFor($teacher)); // Owner
        $this->assertTrue($exam->hasAccessFor($otherTeacher)); // Other user
    }

    public function test_paid_exam_requires_purchase()
    {
        $teacher = Teacher::factory()->create();
        $otherTeacher = Teacher::factory()->create();
        $exam = Exam::factory()->create(['teacher_id' => $teacher->id, 'monetize' => 'paid']);

        $this->assertTrue($exam->hasAccessFor($teacher)); // Owner has access
        $this->assertFalse($exam->hasAccessFor($otherTeacher)); // Other user needs to purchase
    }

    public function test_exam_purchase_grants_access()
    {
        $teacher = Teacher::factory()->create();
        $otherTeacher = Teacher::factory()->create();
        $exam = Exam::factory()->create(['teacher_id' => $teacher->id, 'monetize' => 'paid', 'price' => 100]);

        // Initially no access
        $this->assertFalse($exam->hasAccessFor($otherTeacher));

        // Create purchase record
        $invoice = Invoice::factory()->create([
            'teacher_id' => $otherTeacher->id,
            'exam_id' => $exam->id,
            'amount' => 100,
            'status' => 'paid'
        ]);

        ExamPurchase::create([
            'exam_id' => $exam->id,
            'purchaser_id' => $otherTeacher->id,
            'purchaser_type' => 'teacher',
            'invoice_id' => $invoice->id,
            'purchase_price' => 100,
            'purchased_at' => now()
        ]);

        // Now should have access
        $this->assertTrue($exam->hasAccessFor($otherTeacher));
        $this->assertTrue($exam->isPurchasedBy($otherTeacher->id, 'teacher'));
    }

    public function test_institute_exam_purchase()
    {
        $teacher = Teacher::factory()->create();
        $institute = Institute::factory()->create();
        $exam = Exam::factory()->create(['teacher_id' => $teacher->id, 'monetize' => 'paid', 'price' => 200]);

        // Create purchase record for institute
        $invoice = Invoice::factory()->create([
            'institute_id' => $institute->id,
            'exam_id' => $exam->id,
            'amount' => 200,
            'status' => 'paid'
        ]);

        ExamPurchase::create([
            'exam_id' => $exam->id,
            'purchaser_id' => $institute->id,
            'purchaser_type' => 'institute',
            'invoice_id' => $invoice->id,
            'purchase_price' => 200,
            'purchased_at' => now()
        ]);

        $this->assertTrue($exam->hasAccessFor($institute));
        $this->assertTrue($exam->isPurchasedBy($institute->id, 'institute'));
    }

    public function test_purchased_exams_relationship()
    {
        $teacher = Teacher::factory()->create();
        $exam1 = Exam::factory()->create(['monetize' => 'paid', 'price' => 100]);
        $exam2 = Exam::factory()->create(['monetize' => 'paid', 'price' => 150]);

        // Create purchases
        $invoice1 = Invoice::factory()->create(['teacher_id' => $teacher->id, 'exam_id' => $exam1->id]);
        $invoice2 = Invoice::factory()->create(['teacher_id' => $teacher->id, 'exam_id' => $exam2->id]);

        ExamPurchase::create([
            'exam_id' => $exam1->id,
            'purchaser_id' => $teacher->id,
            'purchaser_type' => 'teacher',
            'invoice_id' => $invoice1->id,
            'purchase_price' => 100,
            'purchased_at' => now()
        ]);

        ExamPurchase::create([
            'exam_id' => $exam2->id,
            'purchaser_id' => $teacher->id,
            'purchaser_type' => 'teacher',
            'invoice_id' => $invoice2->id,
            'purchase_price' => 150,
            'purchased_at' => now()
        ]);

        $purchasedExams = $teacher->purchasedExams;
        $this->assertCount(2, $purchasedExams);
        $this->assertTrue($purchasedExams->contains($exam1));
        $this->assertTrue($purchasedExams->contains($exam2));
    }

    public function test_course_purchase_system()
    {
        $teacher = Teacher::factory()->create();
        $otherTeacher = Teacher::factory()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id, 'monetize' => 'paid', 'price' => 300]);

        // Initially no access
        $this->assertFalse($course->hasAccessFor($otherTeacher));

        // Create purchase
        $invoice = Invoice::factory()->create([
            'teacher_id' => $otherTeacher->id,
            'course_id' => $course->id,
            'amount' => 300,
            'status' => 'paid'
        ]);

        CoursePurchase::create([
            'course_id' => $course->id,
            'purchaser_id' => $otherTeacher->id,
            'purchaser_type' => 'teacher',
            'invoice_id' => $invoice->id,
            'purchase_price' => 300,
            'purchased_at' => now()
        ]);

        // Now should have access
        $this->assertTrue($course->hasAccessFor($otherTeacher));
        $this->assertTrue($course->isPurchasedBy($otherTeacher->id, 'teacher'));
    }
}