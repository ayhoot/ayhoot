<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Course;
use App\Models\ExamPurchase;
use App\Models\CoursePurchase;
use App\Models\PaymentSession;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Institute;
use App\Service\Zarinpal;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $zarinpal;

    public function __construct()
    {
        $this->zarinpal = new Zarinpal();
    }

    /**
     * Initiate purchase for content (exam or course)
     */
    public function initiatePurchase($user, $contentType, $contentId, $amount, $voucherCode = null)
    {
        try {
            // Validate content exists
            $content = $this->getContent($contentType, $contentId);
            if (!$content) {
                throw new \Exception('Content not found');
            }

            // Check if already purchased
            if ($this->isAlreadyPurchased($user, $contentType, $contentId)) {
                throw new \Exception('Content already purchased');
            }

            // Apply discount if voucher provided
            $finalAmount = $amount;
            $discountData = null;
            
            if ($voucherCode) {
                $discountService = new DiscountService();
                $discountData = $discountService->validateDiscountCode($voucherCode, $contentType, $amount);
                $finalAmount = $discountData['final_amount'];
            }

            // If amount is 0 (free or fully discounted), grant access immediately
            if ($finalAmount <= 0) {
                $this->grantAccess($user, $contentType, $contentId, 0, $voucherCode, $discountData);
                return [
                    'success' => true,
                    'payment_url' => null,
                    'final_amount' => 0,
                    'message' => 'Access granted successfully'
                ];
            }

            // Create payment with ZarinPal
            $userType = $this->getUserType($user);
            $redirectUrl = url("/api/payment/verify/{$userType}");
            
            $result = $this->zarinpal->request(
                $finalAmount,
                "Purchase {$contentType}: {$content->title}",
                $user->email ?? '',
                $user->phone ?? '',
                $redirectUrl
            );

            if ($result['success']) {
                // Store payment session
                $paymentSession = PaymentSession::createSession($result['authority'], [
                    'user_id' => $user->id,
                    'user_type' => $userType,
                    'content_type' => $contentType,
                    'content_id' => $contentId,
                    'amount' => $finalAmount,
                    'original_amount' => $amount,
                    'discount_code' => $voucherCode,
                    'discount_amount' => $discountData ? $discountData['discount_amount'] : 0,
                    'description' => "Purchase {$contentType}: {$content->title}"
                ]);

                return [
                    'success' => true,
                    'payment_url' => $result['start_pay_url'],
                    'final_amount' => $finalAmount,
                    'discount_applied' => $discountData ? true : false,
                    'message' => 'Payment initiated successfully'
                ];
            } else {
                throw new \Exception($result['message'] ?? 'Payment initiation failed');
            }

        } catch (\Exception $e) {
            Log::error('Payment initiation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify payment and grant access
     */
    public function verifyPayment($authority, $status)
    {
        try {
            if ($status !== 'OK') {
                throw new \Exception('Payment was cancelled or failed');
            }

            // Get payment session
            $paymentSession = PaymentSession::getByAuthority($authority);
            if (!$paymentSession) {
                throw new \Exception('Payment session not found');
            }

            // Check if already completed
            if ($paymentSession->isCompleted()) {
                return [
                    'success' => true,
                    'ref_id' => $paymentSession->payment_reference,
                    'message' => 'Payment already completed',
                    'payment_data' => $paymentSession->getPaymentData()
                ];
            }

            // Verify with ZarinPal
            $result = $this->zarinpal->verify($authority, $paymentSession->amount);

            if ($result['success']) {
                // Mark session as completed
                $paymentSession->markCompleted($result['ref_id']);

                // Grant access
                $paymentData = $paymentSession->getPaymentData();
                $user = $this->getUserById($paymentData['user_id'], $paymentData['user_type']);
                
                if ($user) {
                    $this->grantAccess(
                        $user,
                        $paymentData['content_type'],
                        $paymentData['content_id'],
                        $paymentData['amount'],
                        $paymentData['discount_code'],
                        [
                            'discount_amount' => $paymentData['discount_amount'],
                            'final_amount' => $paymentData['amount']
                        ]
                    );

                    // Send notification
                    $this->sendPurchaseNotification($user, $paymentData);
                }

                return [
                    'success' => true,
                    'ref_id' => $result['ref_id'],
                    'message' => 'Payment completed successfully',
                    'payment_data' => $paymentData
                ];
            } else {
                $paymentSession->markFailed();
                throw new \Exception($result['message'] ?? 'Payment verification failed');
            }

        } catch (\Exception $e) {
            Log::error('Payment verification failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Grant access to content after successful payment
     */
    private function grantAccess($user, $contentType, $contentId, $amount, $voucherCode = null, $discountData = null)
    {
        $userType = $this->getUserType($user);

        if ($contentType == 'exam') {
            // For students, also add to pivot table for legacy compatibility
            if ($user instanceof Student) {
                $user->exams()->syncWithoutDetaching([
                    $contentId => [
                        'is_paid' => $amount > 0,
                        'access_at' => now()
                    ]
                ]);
            }

            // Create purchase record
            ExamPurchase::create([
                'exam_id' => $contentId,
                'purchaser_id' => $user->id,
                'purchaser_type' => $userType,
                'amount_paid' => $amount,
                'original_amount' => $discountData ? ($amount + $discountData['discount_amount']) : $amount,
                'discount_code' => $voucherCode,
                'discount_amount' => $discountData ? $discountData['discount_amount'] : 0,
                'purchased_at' => now(),
                'payment_method' => 'zarinpal',
                'payment_status' => 'completed'
            ]);

        } elseif ($contentType == 'course') {
            // For students, also add to pivot table for legacy compatibility
            if ($user instanceof Student) {
                $user->courses()->syncWithoutDetaching($contentId);
            }

            // Create purchase record
            CoursePurchase::create([
                'course_id' => $contentId,
                'purchaser_id' => $user->id,
                'purchaser_type' => $userType,
                'amount_paid' => $amount,
                'original_amount' => $discountData ? ($amount + $discountData['discount_amount']) : $amount,
                'discount_code' => $voucherCode,
                'discount_amount' => $discountData ? $discountData['discount_amount'] : 0,
                'purchased_at' => now(),
                'payment_method' => 'zarinpal',
                'payment_status' => 'completed'
            ]);
        }
    }

    /**
     * Check if user already purchased content
     */
    private function isAlreadyPurchased($user, $contentType, $contentId)
    {
        if ($user instanceof Student) {
            if ($contentType == 'exam') {
                return $user->hasPurchasedExam($contentId);
            } elseif ($contentType == 'course') {
                return $user->hasPurchasedCourse($contentId);
            }
        }

        // For teachers and institutes, check purchase tables
        $userType = $this->getUserType($user);
        
        if ($contentType == 'exam') {
            return ExamPurchase::where('exam_id', $contentId)
                ->where('purchaser_id', $user->id)
                ->where('purchaser_type', $userType)
                ->where('payment_status', 'completed')
                ->exists();
        } elseif ($contentType == 'course') {
            return CoursePurchase::where('course_id', $contentId)
                ->where('purchaser_id', $user->id)
                ->where('purchaser_type', $userType)
                ->where('payment_status', 'completed')
                ->exists();
        }

        return false;
    }

    /**
     * Get content by type and ID
     */
    private function getContent($contentType, $contentId)
    {
        if ($contentType == 'exam') {
            return Exam::find($contentId);
        } elseif ($contentType == 'course') {
            return Course::find($contentId);
        }
        return null;
    }

    /**
     * Get user type string
     */
    private function getUserType($user)
    {
        if ($user instanceof Student) return 'student';
        if ($user instanceof Teacher) return 'teacher';
        if ($user instanceof Institute) return 'institute';
        return null;
    }

    /**
     * Get user by ID and type
     */
    private function getUserById($userId, $userType)
    {
        switch ($userType) {
            case 'student':
                return Student::find($userId);
            case 'teacher':
                return Teacher::find($userId);
            case 'institute':
                return Institute::find($userId);
            default:
                return null;
        }
    }

    /**
     * Send purchase notification
     */
    private function sendPurchaseNotification($user, $paymentData)
    {
        try {
            $content = $this->getContent($paymentData['content_type'], $paymentData['content_id']);
            if ($content) {
                $notificationService = new NotificationService();
                $notificationService->notifyPurchaseCompletion(
                    $user,
                    $content,
                    $paymentData['content_type'],
                    $paymentData['amount']
                );
            }
        } catch (\Exception $e) {
            Log::warning('Failed to send purchase notification: ' . $e->getMessage());
        }
    }
}