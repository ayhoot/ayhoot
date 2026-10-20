<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\Institute;
use App\Models\Exam;
use App\Models\Course;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PaymentSystemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->category = Category::factory()->create();
        $this->teacher = Teacher::factory()->create();
        $this->institute = Institute::factory()->create();
        $this->student = Student::factory()->create();
        
        $this->exam = Exam::factory()->create([
            'teacher_id' => $this->teacher->id,
            'institute_id' => $this->institute->id,
            'category_id' => $this->category->id,
            'monetize' => 'paid',
            'price' => 100000
        ]);
        
        $this->course = Course::factory()->create([
            'teacher_id' => $this->teacher->id,
            'institute_id' => $this->institute->id,
            'category_id' => $this->category->id,
            'monetize' => 'paid',
            'price' => 200000
        ]);
    }

    public function test_student_can_check_exam_access()
    {
        $response = $this->actingAs($this->student, 'student')
            ->getJson("/api/check-exam-access/{$this->exam->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'has_access',
                'is_purchased',
                'is_free',
                'price',
                'monetize'
            ]);
    }

    public function test_student_can_check_course_access()
    {
        $response = $this->actingAs($this->student, 'student')
            ->getJson("/api/check-course-access/{$this->course->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'has_access',
                'is_purchased',
                'is_free',
                'price',
                'monetize'
            ]);
    }

    public function test_student_can_initiate_exam_purchase()
    {
        $response = $this->actingAs($this->student, 'student')
            ->postJson("/api/payment/buy-exam/{$this->exam->id}", [
                'discount_code' => null
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'payment_url',
                'invoice_id'
            ]);
    }

    public function test_student_can_initiate_course_purchase()
    {
        $response = $this->actingAs($this->student, 'student')
            ->postJson("/api/payment/buy-course/{$this->course->id}", [
                'discount_code' => null
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'payment_url',
                'invoice_id'
            ]);
    }

    public function test_teacher_has_access_to_own_exam()
    {
        $response = $this->actingAs($this->teacher, 'teacher')
            ->getJson("/api/check-exam-access/{$this->exam->id}");

        $response->assertStatus(200)
            ->assertJson(['has_access' => true]);
    }

    public function test_institute_has_access_to_own_course()
    {
        $response = $this->actingAs($this->institute, 'institute')
            ->getJson("/api/check-course-access/{$this->course->id}");

        $response->assertStatus(200)
            ->assertJson(['has_access' => true]);
    }

    public function test_free_exam_is_accessible_to_all()
    {
        $freeExam = Exam::factory()->create([
            'teacher_id' => $this->teacher->id,
            'monetize' => 'free'
        ]);

        $response = $this->actingAs($this->student, 'student')
            ->getJson("/api/check-exam-access/{$freeExam->id}");

        $response->assertStatus(200)
            ->assertJson([
                'has_access' => true,
                'is_free' => true
            ]);
    }

    public function test_unauthenticated_user_cannot_access_payment_endpoints()
    {
        $response = $this->postJson("/api/payment/buy-exam/{$this->exam->id}");
        $response->assertStatus(401);

        $response = $this->getJson("/api/check-exam-access/{$this->exam->id}");
        $response->assertStatus(401);
    }
}