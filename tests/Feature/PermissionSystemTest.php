<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\Institute;
use App\Models\Plan;
use App\Services\PermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionSystemTest extends TestCase
{
    /**
     * Test that free plan users are properly limited
     */
    public function test_free_plan_exam_limits()
    {
        $freePlan = Plan::where('is_free', true)->first();
        $this->assertNotNull($freePlan, 'Free plan should exist');

        $teacher = Teacher::where('plan_id', $freePlan->id)->first();
        $this->assertNotNull($teacher, 'Teacher with free plan should exist');

        $permissionService = new PermissionService();
        
        // Check exam permissions
        $examPermission = $permissionService->canCreateExam($teacher);
        $this->assertIsArray($examPermission);
        $this->assertArrayHasKey('allowed', $examPermission);
        $this->assertArrayHasKey('limit', $examPermission);
        $this->assertEquals(5, $examPermission['limit'], 'Free plan should allow 5 exams');

        // Check course permissions
        $coursePermission = $permissionService->canCreateCourse($teacher);
        $this->assertFalse($coursePermission['allowed'], 'Free plan should not allow courses');
        
        // Check monetization permissions
        $monetizePermission = $permissionService->canMonetize($teacher);
        $this->assertFalse($monetizePermission['allowed'], 'Free plan should not allow monetization');
    }

    /**
     * Test permission API endpoints
     */
    public function test_permission_api_endpoints()
    {
        $teacher = Teacher::first();
        $this->assertNotNull($teacher, 'Teacher should exist');

        // Test permissions index endpoint
        $response = $this->actingAs($teacher, 'teacher')
            ->getJson('/api/permissions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'permissions' => [
                    'exams' => ['allowed'],
                    'courses' => ['allowed'],
                    'groups' => ['allowed'],
                    'tutees' => ['allowed'],
                    'monetize' => ['allowed'],
                    'plan' => ['name', 'is_free', 'features']
                ],
                'user_type',
                'success'
            ]);

        // Test usage endpoint
        $response = $this->actingAs($teacher, 'teacher')
            ->getJson('/api/permissions/usage');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'usage',
                'plan',
                'success'
            ]);
    }

    /**
     * Test exam creation with permissions
     */
    public function test_exam_creation_respects_permissions()
    {
        $teacher = Teacher::first();
        $this->assertNotNull($teacher, 'Teacher should exist');

        // Get current exam count
        $currentExams = $teacher->exams()->count();
        $permissionService = new PermissionService();
        $permission = $permissionService->canCreateExam($teacher);

        if ($permission['allowed']) {
            // Should be able to create exam
            $response = $this->actingAs($teacher, 'teacher')
                ->postJson('/api/teacher/exam', [
                    'title' => 'Test Exam',
                    'description' => 'Test Description',
                    'monetize' => 'free',
                    'status' => 'published'
                ]);

            $response->assertStatus(201)
                ->assertJsonStructure([
                    'exam',
                    'message',
                    'permission_info'
                ]);
        } else {
            // Should be blocked from creating exam
            $response = $this->actingAs($teacher, 'teacher')
                ->postJson('/api/teacher/exam', [
                    'title' => 'Test Exam',
                    'description' => 'Test Description',
                    'monetize' => 'free',
                    'status' => 'published'
                ]);

            $response->assertStatus(403)
                ->assertJson([
                    'permission_denied' => true
                ]);
        }
    }
}