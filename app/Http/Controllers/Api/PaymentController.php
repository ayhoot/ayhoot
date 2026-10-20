<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Plan;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Institute;
use App\Models\Exam;
use App\Models\Course;
use App\Models\Transaction;
use App\Models\Setting;
use App\Models\ExamPurchase;
use App\Models\CoursePurchase;
use App\Service\Zarinpal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function verify(Request $request)
    {
        $authority = $request->query('Authority');
        $status = $request->query('Status');
        $role = 'teacher';

        if (auth('institute')->check()) {
            $role = 'institute';
        } elseif (auth('student')->check()) {
            $role = 'student';
        }

        $frontendStatusUrl = env('FRONTEND_URL', 'http://127.0.0.1:8000') . '/payment-status';

        if (!$authority || !$status) {
            return redirect()->away($frontendStatusUrl . '?status=failed&reason=missing_parameters&role=' . $role);
        }

        $transaction = Transaction::where('transaction_id', $authority)->first();
        if (!$transaction) {
            return redirect()->away($frontendStatusUrl . '?status=failed&reason=transaction_not_found&role=' . $role);
        }

        $invoice = $transaction->invoice;
        if (!$invoice) {
            return redirect()->away($frontendStatusUrl . '?status=failed&reason=invoice_not_found&role=' . $role);
        }

        if ($status !== 'OK') {
            $invoice->update(['status' => 'failed']);
            $transaction->update(['status' => 'failed']);
            return redirect()->away($frontendStatusUrl . '?status=failed&reason=payment_canceled&role=' . $role);
        }

        $zarinpal = new Zarinpal();
        $result = $zarinpal->verify($authority, $invoice->amount);

        if ($result['success']) {
            $invoice->update(['status' => 'paid']);
            $transaction->update([
                'status' => 'successful',
                'ref_id' => $result['ref_id'] ?? null
            ]);

            // Handle different types of purchases
            if ($invoice->plan_id) {
                $this->activatePlan($invoice->teacher, $invoice->plan);
                return redirect()->away($frontendStatusUrl . '?status=success&ref_id=' . $result['ref_id'] . '&plan_id=' . $invoice->plan_id . '&role=' . $role);
            } elseif ($invoice->exam_id) {
                $this->grantExamAccess($invoice);
                // Redirect to payment status page with exam details
                return redirect()->away($frontendStatusUrl . '?status=success&ref_id=' . $result['ref_id'] . '&exam_id=' . $invoice->exam_id . '&role=' . $role);
            } elseif ($invoice->course_id) {
                $this->grantCourseAccess($invoice);
                // Redirect to payment status page with course details
                return redirect()->away($frontendStatusUrl . '?status=success&ref_id=' . $result['ref_id'] . '&course_id=' . $invoice->course_id . '&role=' . $role);
            }

            return redirect()->away($frontendStatusUrl . '?status=success&ref_id=' . $result['ref_id'] . '&role=' . $role);
        } else {
            $invoice->update(['status' => 'failed']);
            $transaction->update(['status' => 'failed']);
            return redirect()->away($frontendStatusUrl . '?status=failed&reason=' . urlencode($result['message']));
        }
    }

    public function activatePlan(object $user, Plan $plan)
    {
        $user->plan_id = $plan->id;
        $user->save();
    }

    private function grantExamAccess(Invoice $invoice)
    {
        if ($invoice->student_id) {
            $student = Student::find($invoice->student_id);
            if ($student && $invoice->exam_id) {
                $student->exams()->syncWithoutDetaching([
                    $invoice->exam_id => [
                        'is_paid' => true,
                        'access_at' => now()
                    ]
                ]);
            }
        }

        // For teachers and institutes, create purchase record instead of duplicating
        if ($invoice->teacher_id || $invoice->institute_id) {
            $this->createExamPurchaseRecord($invoice);
        }
    }

    private function createExamPurchaseRecord(Invoice $invoice)
    {
        try {
            $exam = Exam::find($invoice->exam_id);
            if (!$exam) {
                return;
            }

            $purchaserType = $invoice->teacher_id ? 'teacher' : 'institute';
            $purchaserId = $invoice->teacher_id ?: $invoice->institute_id;

            ExamPurchase::create([
                'exam_id' => $invoice->exam_id,
                'purchaser_id' => $purchaserId,
                'purchaser_type' => $purchaserType,
                'invoice_id' => $invoice->id,
                'purchase_price' => $invoice->amount,
                'purchased_at' => now()
            ]);

            \Log::info("Exam purchase recorded", [
                'exam_id' => $invoice->exam_id,
                'purchaser_id' => $purchaserId,
                'purchaser_type' => $purchaserType,
                'invoice_id' => $invoice->id
            ]);
        } catch (\Exception $e) {
            \Log::error("Failed to create exam purchase record", [
                'invoice_id' => $invoice->id,
                'exam_id' => $invoice->exam_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function grantCourseAccess(Invoice $invoice)
    {
        if ($invoice->student_id) {
            $student = Student::find($invoice->student_id);
            if ($student && $invoice->course_id) {
                $student->courses()->syncWithoutDetaching($invoice->course_id);
            }
        }

        // For teachers and institutes, create purchase record
        if ($invoice->teacher_id || $invoice->institute_id) {
            $this->createCoursePurchaseRecord($invoice);
        }
    }

    private function createCoursePurchaseRecord(Invoice $invoice)
    {
        try {
            $course = Course::find($invoice->course_id);
            if (!$course) {
                return;
            }

            $purchaserType = $invoice->teacher_id ? 'teacher' : 'institute';
            $purchaserId = $invoice->teacher_id ?: $invoice->institute_id;

            CoursePurchase::create([
                'course_id' => $invoice->course_id,
                'purchaser_id' => $purchaserId,
                'purchaser_type' => $purchaserType,
                'invoice_id' => $invoice->id,
                'purchase_price' => $invoice->amount,
                'purchased_at' => now()
            ]);

            \Log::info("Course purchase recorded", [
                'course_id' => $invoice->course_id,
                'purchaser_id' => $purchaserId,
                'purchaser_type' => $purchaserType,
                'invoice_id' => $invoice->id
            ]);
        } catch (\Exception $e) {
            \Log::error("Failed to create course purchase record", [
                'invoice_id' => $invoice->id,
                'course_id' => $invoice->course_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function buyExam(Request $request, Exam $exam)
    {
        $request->validate([
            'discount_code' => 'nullable|string'
        ]);

        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return response(['message' => __('messages.unauthenticated')], 401);
        }

        // Check if exam is paid
        if ($exam->monetize !== 'paid') {
            return response(['message' => __('messages.exam_is_free')], 400);
        }

        // Check if user already purchased this exam
        if ($this->hasUserPurchased($user, 'exam', $exam->id)) {
            return response(['message' => __('messages.already_purchased')], 400);
        }

        $finalPrice = $this->calculateExamPrice($exam, $request->discount_code);

        $invoice = Invoice::create([
            $this->getUserTypeColumn($user) => $user->id,
            'exam_id' => $exam->id,
            'invoice_number' => 'INV-' . strtoupper(uniqid()),
            'amount' => $finalPrice,
            'status' => 'pending',
            'description' => "Purchase Exam: {$exam->title}",
            'currency' => 'IRT',
            'meta' => [
                'type' => 'exam',
                'discount_code' => $request->discount_code
            ]
        ]);

        return $this->initiatePayment($invoice, $user);
    }

    public function buyCourse(Request $request, Course $course)
    {
        $request->validate([
            'discount_code' => 'nullable|string'
        ]);

        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return response(['message' => __('messages.unauthenticated')], 401);
        }

        // Check if course is paid
        if ($course->monetize !== 'paid') {
            return response(['message' => __('messages.course_is_free')], 400);
        }

        // Check if user already purchased this course
        if ($this->hasUserPurchased($user, 'course', $course->id)) {
            return response(['message' => __('messages.already_purchased')], 400);
        }

        $finalPrice = $this->calculateCoursePrice($course, $request->discount_code);

        $invoice = Invoice::create([
            $this->getUserTypeColumn($user) => $user->id,
            'course_id' => $course->id,
            'invoice_number' => 'INV-' . strtoupper(uniqid()),
            'amount' => $finalPrice,
            'status' => 'pending',
            'description' => "Purchase Course: {$course->title}",
            'currency' => 'IRT',
            'meta' => [
                'type' => 'course',
                'discount_code' => $request->discount_code
            ]
        ]);

        return $this->initiatePayment($invoice, $user);
    }

    private function getAuthenticatedUser()
    {
        if (auth('student')->check()) {
            return auth('student')->user();
        } elseif (auth('teacher')->check()) {
            return auth('teacher')->user();
        } elseif (auth('institute')->check()) {
            return auth('institute')->user();
        }
        return null;
    }

    private function getUserTypeColumn($user)
    {
        if ($user instanceof Student) return 'student_id';
        if ($user instanceof Teacher) return 'teacher_id';
        if ($user instanceof Institute) return 'institute_id';
        return null;
    }

    private function hasUserPurchased($user, $type, $itemId)
    {
        if ($user instanceof Student) {
            if ($type == 'exam') {
                return $user->exams()->where('exam_id', $itemId)->exists();
            } elseif ($type == 'course') {
                return $user->courses()->where('course_id', $itemId)->exists();
            }
        }
        // Teachers and institutes have access to their own content
        return false;
    }

    private function calculateExamPrice(Exam $exam, $discountCode = null)
    {
        $basePrice = (float)$exam->price;

        if ($discountCode && !empty($exam->discounts)) {
            $discounts = is_array($exam->discounts) ? $exam->discounts : json_decode($exam->discounts, true);
            if (isset($discounts[$discountCode])) {
                $discountPercent = $discounts[$discountCode];
                $basePrice = $basePrice * (1 - $discountPercent / 100);
            }
        }

        return max(0, $basePrice);
    }

    private function calculateCoursePrice(Course $course, $discountCode = null)
    {
        $basePrice = (float)$course->price;

        // Apply discount logic similar to exam if course has discounts
        return max(0, $basePrice);
    }

    private function initiatePayment(Invoice $invoice, $user)
    {
        $zarinpal = new Zarinpal();
        $result = $zarinpal->request(
            $invoice->amount,
            $invoice->description,
            $user->email ?? 'noemail@example.com'
        );

        if ($result['success']) {
            Transaction::create([
                'invoice_id' => $invoice->id,
                'gateway' => 'zarinpal',
                'transaction_id' => $result['authority'],
                'amount' => $invoice->amount,
                'status' => 'pending'
            ]);

            return response([
                'success' => true,
                'payment_url' => $result['start_pay_url'],
                'invoice_id' => $invoice->id
            ]);
        } else {
            $invoice->update(['status' => 'failed']);
            return response([
                'success' => false,
                'message' => $result['message'] ?? 'Payment initiation failed'
            ], 500);
        }
    }

    private function validateVoucherCode($voucherCode, $planId)
    {
        $settings = Setting::first();
        $discounts = $settings->discount_settings ?? [];

        foreach ($discounts as $discount) {
            if ($discount['code'] == $voucherCode) {
                $currentDate = now();
                $startDate = $discount['startDate'] ? Carbon::parse($discount['startDate']) : null;
                $endDate = $discount['endDate'] ? Carbon::parse($discount['endDate']) : null;

                if ($startDate && $currentDate->lt($startDate)) {
                    return ['valid' => false, 'message' => 'Voucher code is not yet active'];
                }

                if ($endDate && $currentDate->gt($endDate)) {
                    return ['valid' => false, 'message' => 'Voucher code has expired'];
                }

                if (!empty($discount['plans']) && !in_array($planId, $discount['plans'])) {
                    return ['valid' => false, 'message' => 'Voucher code is not applicable to this plan'];
                }

                return [
                    'valid' => true,
                    'percentage' => $discount['percentage'],
                    'discount' => $discount
                ];
            }
        }

        return ['valid' => false, 'message' => 'Invalid voucher code'];
    }

    public function calculateFinalPrice(Plan $plan, object $teacher, array $data): float
    {
        $basePrice = (float)$plan->price;
        $duration = (int)$data['duration'];
        $useWallet = (bool)$data['use_wallet'];
        $voucherCode = $data['voucher_code'] ?? null;

        $subtotal = $basePrice * $duration;

        $durationDiscountRate = 0;
        if ($duration == 3) $durationDiscountRate = 0.10;
        if ($duration == 12) $durationDiscountRate = 0.20;

        $priceAfterDurationDiscount = $subtotal * (1 - $durationDiscountRate);

        $voucherDiscountAmount = 0;
        if ($voucherCode) {
            $voucherValidation = $this->validateVoucherCode($voucherCode, $plan->id);

            if ($voucherValidation['valid']) {
                $voucherDiscountAmount = ($priceAfterDurationDiscount * $voucherValidation['percentage']) / 100;
            } else {
                throw new \Exception($voucherValidation['message']);
            }
        }

        $priceAfterVoucher = max(0, $priceAfterDurationDiscount - $voucherDiscountAmount);

        $walletDeduction = 0;
        if ($useWallet) {
            $walletBalance = (float)$teacher->wallet;
            $walletDeduction = min($priceAfterVoucher, $walletBalance);
        }

        return max(0, $priceAfterVoucher - $walletDeduction);
    }

    private function getPurchasedExamsUrl($role)
    {
        $frontendUrl = env('FRONTEND_URL', 'http://127.0.0.1:8000');

        switch ($role) {
            case 'teacher':
                return $frontendUrl . '/teacher/purchased?type=exams';
            case 'institute':
                return $frontendUrl . '/institute/purchased?type=exams';
            case 'student':
                return $frontendUrl . '/student/exams/purchased';
            default:
                return $frontendUrl . '/payment-status';
        }
    }

    private function getPurchasedCoursesUrl($role)
    {
        $frontendUrl = env('FRONTEND_URL', 'http://127.0.0.1:8000');

        switch ($role) {
            case 'teacher':
                return $frontendUrl . '/teacher/purchased?type=courses';
            case 'institute':
                return $frontendUrl . '/institute/purchased?type=courses';
            case 'student':
                return $frontendUrl . '/student/courses/purchased';
            default:
                return $frontendUrl . '/payment-status';
        }
    }
}
