<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Controller;
use App\Services\DiscountService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DiscountController extends Controller
{
    protected $discountService;

    public function __construct(DiscountService $discountService)
    {
        $this->discountService = $discountService;
    }

    /**
     * Validate discount code
     */
    public function validateCode(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'content_type' => 'required|in:exam,course',
            'amount' => 'required|numeric|min:0'
        ]);

        try {
            $result = $this->discountService->validateDiscountCode(
                $request->code,
                $request->content_type,
                $request->amount
            );

            return response()->json([
                'success' => true,
                'discount' => $result,
                'message' => 'Discount code applied successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get active discounts for content type
     */
    public function getActiveDiscounts(Request $request): JsonResponse
    {
        $contentType = $request->query('content_type');
        
        $discounts = $this->discountService->getActiveDiscounts($contentType);

        return response()->json([
            'success' => true,
            'discounts' => $discounts
        ]);
    }
}