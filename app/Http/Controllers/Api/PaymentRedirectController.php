<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentRedirectController extends Controller
{
    /**
     * Handle all payment redirects from Zarinpal
     * This is a public endpoint that doesn't require authentication
     */
    public function handlePaymentRedirect(Request $request, $userType = null)
    {
        $authority = $request->input('Authority');
        $status = $request->input('Status');
        
        \Log::info('Payment redirect received', [
            'authority' => $authority,
            'status' => $status,
            'user_type' => $userType,
            'url' => $request->fullUrl(),
            'all_params' => $request->all()
        ]);
        
        // Validate inputs
        if (!$authority || !$status) {
            return $this->redirectToFrontend(false, 'Missing payment parameters');
        }
        
        if (strlen($authority) !== 36) {
            return $this->redirectToFrontend(false, 'Invalid payment reference');
        }
        
        try {
            // Check payment status
            if ($status !== 'OK') {
                \Log::warning('Payment cancelled or failed', ['status' => $status, 'authority' => $authority]);
                return $this->redirectToFrontend(false, 'Payment was cancelled or failed');
            }
            
            // Get payment data from database
            $paymentSession = \App\Models\PaymentSession::getByAuthority($authority);
            
            if (!$paymentSession) {
                \Log::error('Payment session not found or expired', [
                    'authority' => $authority
                ]);
                return $this->redirectToFrontend(false, 'Payment session expired. Please try again.');
            }
            
            if ($paymentSession->isCompleted()) {
                \Log::info('Payment already processed', [
                    'authority' => $authority,
                    'payment_reference' => $paymentSession->payment_reference
                ]);
                return $this->redirectToFrontend(true, 'Payment already completed', $paymentSession->payment_reference, $paymentSession->toArray());
            }
            
            // Convert to array format for compatibility
            $paymentData = [
                'user_id' => $paymentSession->user_id,
                'user_type' => $paymentSession->user_type,
                'content_type' => $paymentSession->content_type,
                'content_id' => $paymentSession->content_id,
                'amount' => $paymentSession->amount,
                'original_amount' => $paymentSession->original_amount,
                'discount_code' => $paymentSession->discount_code,
                'discount_amount' => $paymentSession->discount_amount
            ];
            
            // This check is now handled above when getting the payment session
            
            // Verify with Zarinpal
            $zarinpal = new \App\Service\Zarinpal();
            $verificationResult = $zarinpal->verify($authority, $paymentData['amount']);
            
            if ($verificationResult['success']) {
                // Mark payment session as completed
                $paymentSession->markCompleted($verificationResult['ref_id']);
                
                // Create purchase record
                $this->createPurchaseRecord($paymentData, $verificationResult['ref_id']);
                
                // Send notifications
                $this->sendNotifications($paymentData);
                
                \Log::info('Payment verification successful', [
                    'authority' => $authority,
                    'ref_id' => $verificationResult['ref_id']
                ]);
                
                return $this->redirectToFrontend(true, 'Payment completed successfully', $verificationResult['ref_id'], $paymentData);
                
            } else {
                // Mark payment session as failed
                $paymentSession->markFailed();
                
                \Log::error('Zarinpal verification failed', [
                    'authority' => $authority,
                    'error' => $verificationResult['message'] ?? 'Unknown error'
                ]);
                
                return $this->redirectToFrontend(false, $verificationResult['message'] ?? 'Payment verification failed');
            }
            
        } catch (\Exception $e) {
            \Log::error('Payment verification exception', [
                'authority' => $authority,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->redirectToFrontend(false, 'Payment verification failed. Please contact support.');
        }
    }
    
    private function createPurchaseRecord($paymentData, $refId)
    {
        try {
            if ($paymentData['content_type'] == 'exam') {
                return \App\Models\ExamPurchase::create([
                    'exam_id' => $paymentData['content_id'],
                    'purchaser_id' => $paymentData['user_id'],
                    'purchaser_type' => $paymentData['user_type'],
                    'amount_paid' => $paymentData['amount'],
                    'original_amount' => $paymentData['original_amount'],
                    'discount_code' => $paymentData['discount_code'],
                    'discount_amount' => $paymentData['discount_amount'],
                    'purchased_at' => now(),
                    'payment_method' => 'zarinpal',
                    'payment_status' => 'completed',
                    'payment_reference' => $refId
                ]);
            } elseif ($paymentData['content_type'] == 'course') {
                return \App\Models\CoursePurchase::create([
                    'course_id' => $paymentData['content_id'],
                    'purchaser_id' => $paymentData['user_id'],
                    'purchaser_type' => $paymentData['user_type'],
                    'amount_paid' => $paymentData['amount'],
                    'original_amount' => $paymentData['original_amount'],
                    'discount_code' => $paymentData['discount_code'],
                    'discount_amount' => $paymentData['discount_amount'],
                    'purchased_at' => now(),
                    'payment_method' => 'zarinpal',
                    'payment_status' => 'completed',
                    'payment_reference' => $refId
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to create purchase record', [
                'error' => $e->getMessage(),
                'payment_data' => $paymentData
            ]);
        }
        
        return null;
    }
    
    private function sendNotifications($paymentData)
    {
        try {
            // Get user
            $user = null;
            if ($paymentData['user_type'] == 'student') {
                $user = \App\Models\Student::find($paymentData['user_id']);
            } elseif ($paymentData['user_type'] == 'teacher') {
                $user = \App\Models\Teacher::find($paymentData['user_id']);
            } elseif ($paymentData['user_type'] == 'institute') {
                $user = \App\Models\Institute::find($paymentData['user_id']);
            }
            
            // Get content
            $content = null;
            if ($paymentData['content_type'] == 'exam') {
                $content = \App\Models\Exam::find($paymentData['content_id']);
            } elseif ($paymentData['content_type'] == 'course') {
                $content = \App\Models\Course::find($paymentData['content_id']);
            }
            
            if ($user && $content) {
                $notificationService = new \App\Services\NotificationService();
                $notificationService->notifyPurchaseCompletion(
                    $user, 
                    $content, 
                    $paymentData['content_type'],
                    $paymentData['amount']
                );
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send purchase notifications', ['error' => $e->getMessage()]);
        }
    }
    
    private function redirectToFrontend($success, $message, $refId = null, $paymentData = null)
    {
        $params = [
            'success' => $success ? 'true' : 'false',
            'message' => $message // Don't double-encode, http_build_query will handle it
        ];
        
        if ($refId) {
            $params['ref_id'] = $refId;
        }
        
        if ($paymentData) {
            $params['content_type'] = $paymentData['content_type'];
            $params['content_id'] = $paymentData['content_id'];
            $params['user_type'] = $paymentData['user_type'];
        }
        
        // Build the frontend URL - http_build_query handles proper encoding
        $frontendUrl = env('FRONTEND_URL', request()->getSchemeAndHttpHost());
        $redirectUrl = $frontendUrl . '/payment-result?' . http_build_query($params);
        
        \Log::info('Redirecting to frontend', [
            'url' => $redirectUrl,
            'params' => $params
        ]);
        
        return redirect($redirectUrl);
    }
}