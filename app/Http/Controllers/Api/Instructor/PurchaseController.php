<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function checkExamAccess(Request $request, Exam $exam)
    {
        $user = $this->getAuthenticatedUser();
        
        if (!$user) {
            return response(['has_access' => false, 'message' => __('messages.unauthenticated')], 401);
        }

        $hasAccess = $this->hasExamAccess($user, $exam);
        $isPurchased = false;

        if ($user instanceof Student && $exam->monetize == 'paid') {
            $isPurchased = $user->exams()->where('exam_id', $exam->id)->exists();
        }

        return response([
            'has_access' => $hasAccess,
            'is_purchased' => $isPurchased,
            'is_free' => $exam->monetize == 'free',
            'price' => $exam->price,
            'monetize' => $exam->monetize
        ]);
    }

    public function checkCourseAccess(Request $request, Course $course)
    {
        $user = $this->getAuthenticatedUser();
        
        if (!$user) {
            return response(['has_access' => false, 'message' => __('messages.unauthenticated')], 401);
        }

        $hasAccess = $this->hasCourseAccess($user, $course);
        $isPurchased = false;

        if ($user instanceof Student && $course->monetize == 'paid') {
            // Check both old pivot table and new purchase table
            $isPurchased = $user->courses()->where('course_id', $course->id)->exists() ||
                          \App\Models\CoursePurchase::where('course_id', $course->id)
                              ->where('purchaser_id', $user->id)
                              ->where('purchaser_type', 'App\Models\Student')
                              ->where('payment_status', 'completed')
                              ->exists();
        }

        return response([
            'has_access' => $hasAccess,
            'is_purchased' => $isPurchased,
            'is_free' => $course->monetize == 'free',
            'price' => $course->price,
            'monetize' => $course->monetize
        ]);
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

    private function hasExamAccess($user, Exam $exam)
    {
        // Teachers and institutes have access to their own exams
        if ($user instanceof \App\Models\Teacher && $exam->teacher_id == $user->id) {
            return true;
        }

        if ($user instanceof \App\Models\Institute && $exam->institute_id == $user->id) {
            return true;
        }

        // Students need to purchase paid exams
        if ($user instanceof Student) {
            if ($exam->monetize == 'free') {
                return true;
            }
            
            return $user->exams()->where('exam_id', $exam->id)->exists();
        }

        return false;
    }

    private function hasCourseAccess($user, Course $course)
    {
        // Teachers and institutes have access to their own courses
        if ($user instanceof \App\Models\Teacher && $course->teacher_id == $user->id) {
            return true;
        }

        if ($user instanceof \App\Models\Institute && $course->institute_id == $user->id) {
            return true;
        }

        // Students need to purchase paid courses
        if ($user instanceof Student) {
            if ($course->monetize == 'free') {
                return true;
            }
            
            // Check both old pivot table and new purchase table
            return $user->courses()->where('course_id', $course->id)->exists() ||
                   \App\Models\CoursePurchase::where('course_id', $course->id)
                       ->where('purchaser_id', $user->id)
                       ->where('purchaser_type', 'App\Models\Student')
                       ->where('payment_status', 'completed')
                       ->exists();
        }

        return false;
    }

    public function initiatePurchase(Request $request)
    {
        $request->validate([
            'content_type' => 'required|in:exam,course',
            'content_id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
            'voucher_code' => 'nullable|string'
        ]);

        try {
            $user = $this->getAuthenticatedUser();
            
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $paymentService = new \App\Services\PaymentService();
            $result = $paymentService->initiatePurchase(
                $user,
                $request->content_type,
                $request->content_id,
                $request->amount,
                $request->voucher_code
            );

            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error('Purchase initiation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function verifyPurchase(Request $request)
    {
        // Handle both GET (from Zarinpal redirect) and POST (from API) requests
        $authority = $request->input('Authority') ?: $request->input('authority');
        $status = $request->input('Status') ?: $request->input('status');
        
        if (!$authority || !$status) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Authority and status are required'], 400);
            } else {
                return $this->redirectToPaymentResult(false, 'Missing payment parameters');
            }
        }
        
        if (strlen($authority) !== 36) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Invalid authority format'], 400);
            } else {
                return $this->redirectToPaymentResult(false, 'Invalid payment reference');
            }
        }

        try {
            \Log::info('Payment verification started', [
                'authority' => $authority,
                'status' => $status,
                'user' => auth()->id(),
                'method' => $request->method()
            ]);

            if ($status !== 'OK') {
                \Log::warning('Payment cancelled or failed', ['status' => $status]);
                $message = 'Payment was cancelled or failed';
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                        'status' => $status
                    ], 400);
                } else {
                    return $this->redirectToPaymentResult(false, $message);
                }
            }

            // Check if this purchase was already processed
            $existingPurchase = $this->checkExistingPurchase($authority);
            if ($existingPurchase) {
                \Log::info('Purchase already processed', ['authority' => $authority]);
                $message = 'Purchase already completed';
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $message,
                        'ref_id' => $existingPurchase['ref_id'],
                        'already_processed' => true
                    ]);
                } else {
                    return $this->redirectToPaymentResult(true, $message, $existingPurchase['ref_id']);
                }
            }

            // Verify payment with ZarinPal
            $verificationResult = $this->verifyZarinPalPayment($authority);

            if ($verificationResult['success']) {
                // Create purchase record
                $purchaseRecord = $this->createPurchaseRecord($verificationResult['payment_data']);

                // Send purchase notifications
                $user = $this->getAuthenticatedUser();
                $content = $this->getContent(
                    $verificationResult['payment_data']['content_type'], 
                    $verificationResult['payment_data']['content_id']
                );
                
                if ($user && $content) {
                    $notificationService = new \App\Services\NotificationService();
                    $notificationService->notifyPurchaseCompletion(
                        $user, 
                        $content, 
                        $verificationResult['payment_data']['content_type'],
                        $verificationResult['payment_data']['amount']
                    );
                }

                \Log::info('Purchase completed successfully', [
                    'authority' => $authority,
                    'ref_id' => $verificationResult['ref_id'],
                    'purchase_id' => $purchaseRecord->id ?? null
                ]);

                $message = 'Purchase completed successfully';
                $refId = $verificationResult['ref_id'];

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $message,
                        'ref_id' => $refId,
                        'purchase_details' => [
                            'content_type' => $verificationResult['payment_data']['content_type'],
                            'content_id' => $verificationResult['payment_data']['content_id'],
                            'amount_paid' => $verificationResult['payment_data']['amount'],
                            'discount_applied' => !empty($verificationResult['payment_data']['discount_code'])
                        ]
                    ]);
                } else {
                    return $this->redirectToPaymentResult(true, $message, $refId, $verificationResult['payment_data']);
                }
            } else {
                \Log::error('Payment verification failed', [
                    'authority' => $authority,
                    'error' => $verificationResult['message'] ?? 'Unknown error'
                ]);
                
                $message = $verificationResult['message'] ?? 'Payment verification failed';
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ], 400);
                } else {
                    return $this->redirectToPaymentResult(false, $message);
                }
            }

        } catch (\Exception $e) {
            \Log::error('Purchase verification exception', [
                'authority' => $authority,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $message = 'Failed to verify purchase';
            
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 500);
            } else {
                return $this->redirectToPaymentResult(false, $message);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verifyPaymentRedirect(Request $request)
    {
        // This method handles payment verification from Zarinpal redirects (no auth required)
        $authority = $request->input('Authority');
        $status = $request->input('Status');
        
        \Log::info('Public payment verification redirect', [
            'authority' => $authority,
            'status' => $status,
            'url' => $request->fullUrl()
        ]);
        
        if (!$authority || !$status) {
            return $this->redirectToPaymentResult(false, 'Missing payment parameters');
        }
        
        if (strlen($authority) !== 36) {
            return $this->redirectToPaymentResult(false, 'Invalid payment reference');
        }

        try {
            $paymentService = new \App\Services\PaymentService();
            $result = $paymentService->verifyPayment($authority, $status);

            return $this->redirectToPaymentResult(
                $result['success'],
                $result['message'],
                $result['ref_id'] ?? null,
                $result['payment_data'] ?? null
            );

        } catch (\Exception $e) {
            \Log::error('Payment verification exception in redirect', [
                'authority' => $authority,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->redirectToPaymentResult(false, 'Failed to verify purchase: ' . $e->getMessage());
        }
    }

    private function sendPurchaseNotifications($paymentData)
    {
        try {
            // Try to find the user and content to send notifications
            $userType = $paymentData['user_type'];
            $userId = $paymentData['user_id'];
            
            $user = null;
            if ($userType == 'student') {
                $user = \App\Models\Student::find($userId);
            } elseif ($userType == 'teacher') {
                $user = \App\Models\Teacher::find($userId);
            } elseif ($userType == 'institute') {
                $user = \App\Models\Institute::find($userId);
            }
            
            if ($user) {
                $content = $this->getContent($paymentData['content_type'], $paymentData['content_id']);
                
                if ($content) {
                    $notificationService = new \App\Services\NotificationService();
                    $notificationService->notifyPurchaseCompletion(
                        $user, 
                        $content, 
                        $paymentData['content_type'],
                        $paymentData['amount']
                    );
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send purchase notifications', ['error' => $e->getMessage()]);
        }
    }

    private function redirectToPaymentResult($success, $message, $refId = null, $purchaseData = null)
    {
        // Redirect to a frontend page that shows the payment result
        $params = [
            'success' => $success ? 'true' : 'false',
            'message' => $message // Don't double-encode, http_build_query will handle it
        ];
        
        if ($refId) {
            $params['ref_id'] = $refId;
        }
        
        if ($purchaseData) {
            $params['content_type'] = $purchaseData['content_type'];
            $params['content_id'] = $purchaseData['content_id'];
            $params['user_type'] = $purchaseData['user_type'];
        }
        
        // Use the same domain as the current request for the frontend
        $frontendUrl = env('FRONTEND_URL', request()->getSchemeAndHttpHost());
        $redirectUrl = $frontendUrl . '/payment-result?' . http_build_query($params);
        
        \Log::info('Redirecting to payment result', [
            'url' => $redirectUrl,
            'params' => $params
        ]);
        
        return redirect($redirectUrl);
    }

    private function checkExistingPurchase($authority)
    {
        // Check if this authority was already processed using database
        $paymentSession = \App\Models\PaymentSession::where('authority', $authority)
                                                   ->where('status', 'completed')
                                                   ->first();
        
        if ($paymentSession) {
            return [
                'ref_id' => $paymentSession->payment_reference,
                'processed_at' => $paymentSession->updated_at->toISOString()
            ];
        }
        
        return null;
    }

    private function getUserType($user)
    {
        if ($user instanceof \App\Models\Student) return 'student';
        if ($user instanceof \App\Models\Teacher) return 'teacher';
        if ($user instanceof \App\Models\Institute) return 'institute';
        return null;
    }

    private function getContent($type, $id)
    {
        if ($type == 'exam') {
            return Exam::find($id);
        } elseif ($type == 'course') {
            return Course::find($id);
        }
        return null;
    }

    private function isAlreadyPurchased($user, $contentType, $contentId)
    {
        if (!($user instanceof Student)) {
            return false; // Teachers and institutes don't need to purchase
        }

        if ($contentType == 'exam') {
            return $user->exams()->where('exam_id', $contentId)->exists();
        } elseif ($contentType == 'course') {
            return $user->courses()->where('course_id', $contentId)->exists();
        }

        return false;
    }

    private function createZarinPalPayment($data)
    {
        try {
            $zarinpal = new \App\Service\Zarinpal();
            $redirectUrl = url("/api/payment/verify/{$data['user_type']}");
            
            // Create payment request with ZarinPal
            $result = $zarinpal->request(
                $data['amount'],
                $data['description'],
                '', // email - optional
                '', // mobile - optional
                $redirectUrl
            );
            
            if ($result['success']) {
                // Store payment data in database for verification later
                $paymentSession = \App\Models\PaymentSession::createSession($result['authority'], $data);
                
                \Log::info('Payment session created in database', [
                    'authority' => $result['authority'],
                    'payment_session_id' => $paymentSession->id,
                    'user_id' => $data['user_id'],
                    'user_type' => $data['user_type'],
                    'content_type' => $data['content_type'],
                    'content_id' => $data['content_id']
                ]);
                
                return $result['start_pay_url'];
            } else {
                throw new \Exception($result['message'] ?? 'Payment creation failed');
            }
            
        } catch (\Exception $e) {
            \Log::error('ZarinPal payment creation failed: ' . $e->getMessage());
            throw new \Exception('Failed to create payment: ' . $e->getMessage());
        }
    }

    private function verifyZarinPalPayment($authority)
    {
        try {
            // Get stored payment data from database
            $paymentSession = \App\Models\PaymentSession::getByAuthority($authority);
            
            if (!$paymentSession) {
                throw new \Exception('Payment session not found or expired');
            }
            
            if ($paymentSession->isCompleted()) {
                return [
                    'success' => true,
                    'ref_id' => $paymentSession->payment_reference,
                    'payment_data' => [
                        'user_id' => $paymentSession->user_id,
                        'user_type' => $paymentSession->user_type,
                        'content_type' => $paymentSession->content_type,
                        'content_id' => $paymentSession->content_id,
                        'amount' => $paymentSession->amount,
                        'original_amount' => $paymentSession->original_amount,
                        'discount_code' => $paymentSession->discount_code,
                        'discount_amount' => $paymentSession->discount_amount
                    ]
                ];
            }
            
            $zarinpal = new \App\Service\Zarinpal();
            
            // Verify payment with ZarinPal
            $result = $zarinpal->verify($authority, $paymentSession->amount);
            
            if ($result['success']) {
                // Mark payment session as completed
                $paymentSession->markCompleted($result['ref_id']);
                
                return [
                    'success' => true,
                    'ref_id' => $result['ref_id'],
                    'payment_data' => [
                        'user_id' => $paymentSession->user_id,
                        'user_type' => $paymentSession->user_type,
                        'content_type' => $paymentSession->content_type,
                        'content_id' => $paymentSession->content_id,
                        'amount' => $paymentSession->amount,
                        'original_amount' => $paymentSession->original_amount,
                        'discount_code' => $paymentSession->discount_code,
                        'discount_amount' => $paymentSession->discount_amount
                    ]
                ];
            } else {
                // Mark payment session as failed
                $paymentSession->markFailed();
                throw new \Exception($result['message'] ?? 'Payment verification failed');
            }
            
        } catch (\Exception $e) {
            \Log::error('ZarinPal payment verification failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function createPurchaseRecord($paymentData)
    {
        $purchaseRecord = null;
        
        // Convert user type to full class name for morphTo relationship
        $purchaserTypeMap = [
            'student' => 'App\Models\Student',
            'teacher' => 'App\Models\Teacher',
            'institute' => 'App\Models\Institute'
        ];
        
        $purchaserType = $purchaserTypeMap[$paymentData['user_type']] ?? $paymentData['user_type'];
        
        if ($paymentData['content_type'] == 'exam') {
            $purchaseRecord = \App\Models\ExamPurchase::create([
                'exam_id' => $paymentData['content_id'],
                'purchaser_id' => $paymentData['user_id'],
                'purchaser_type' => $purchaserType,
                'amount_paid' => $paymentData['amount'],
                'original_amount' => $paymentData['original_amount'],
                'discount_code' => $paymentData['discount_code'],
                'discount_amount' => $paymentData['discount_amount'],
                'purchased_at' => now(),
                'payment_method' => 'zarinpal',
                'payment_status' => 'completed'
            ]);
        } elseif ($paymentData['content_type'] == 'course') {
            $purchaseRecord = \App\Models\CoursePurchase::create([
                'course_id' => $paymentData['content_id'],
                'purchaser_id' => $paymentData['user_id'],
                'purchaser_type' => $purchaserType,
                'amount_paid' => $paymentData['amount'],
                'original_amount' => $paymentData['original_amount'],
                'discount_code' => $paymentData['discount_code'],
                'discount_amount' => $paymentData['discount_amount'],
                'purchased_at' => now(),
                'payment_method' => 'zarinpal',
                'payment_status' => 'completed'
            ]);
        }
        
        return $purchaseRecord;
    }
}