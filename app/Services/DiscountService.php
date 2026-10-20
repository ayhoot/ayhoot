<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;

class DiscountService
{
    /**
     * Validate and apply discount code
     */
    public function validateDiscountCode($code, $contentType, $amount)
    {
        // Get the first settings record (the existing structure)
        $settings = Setting::first();
        
        if (!$settings || !$settings->discount_settings) {
            throw new \Exception('Discount code not found');
        }
        
        $discounts = $settings->discount_settings;
        $discount = null;

        // Find the discount by code
        foreach ($discounts as $discountItem) {
            if (isset($discountItem['code']) && strtoupper($discountItem['code']) == strtoupper($code)) {
                $discount = $discountItem;
                break;
            }
        }

        if (!$discount) {
            throw new \Exception('Invalid discount code');
        }

        // Check if discount is valid for the content type
        if ($contentType == 'exam' && !($discount['exams'] ?? false)) {
            throw new \Exception('This discount code is not valid for exams');
        }

        if ($contentType == 'course' && !($discount['courses'] ?? false)) {
            throw new \Exception('This discount code is not valid for courses');
        }

        // Check date validity
        $now = Carbon::now();
        
        if (isset($discount['startDate'])) {
            $startDate = Carbon::parse($discount['startDate']);
            if ($now->lt($startDate)) {
                throw new \Exception('Discount code is not yet active');
            }
        }

        if (isset($discount['endDate'])) {
            $endDate = Carbon::parse($discount['endDate']);
            if ($now->gt($endDate)) {
                throw new \Exception('Discount code has expired');
            }
        }

        // Calculate discount amount
        $discountAmount = ($amount * $discount['percentage']) / 100;
        $finalAmount = max(0, $amount - $discountAmount);

        return [
            'code' => $discount['code'],
            'percentage' => $discount['percentage'],
            'discount_amount' => $discountAmount,
            'original_amount' => $amount,
            'final_amount' => $finalAmount,
            'valid' => true
        ];
    }

    /**
     * Get all active discount codes for a content type
     */
    public function getActiveDiscounts($contentType = null)
    {
        try {
            // Get the first settings record (the existing structure)
            $settings = Setting::first();
            
            if (!$settings || !$settings->discount_settings) {
                \Log::info('No discount settings found in database');
                return [];
            }
            
            $discounts = $settings->discount_settings;
        $activeDiscounts = [];
        $now = Carbon::now();

        foreach ($discounts as $discount) {
            // Check date validity
            $isActive = true;
            
            if (isset($discount['startDate'])) {
                $startDate = Carbon::parse($discount['startDate']);
                if ($now->lt($startDate)) {
                    $isActive = false;
                }
            }

            if (isset($discount['endDate'])) {
                $endDate = Carbon::parse($discount['endDate']);
                if ($now->gt($endDate)) {
                    $isActive = false;
                }
            }

            // Check content type validity
            if ($contentType) {
                if ($contentType == 'exam' && !($discount['exams'] ?? false)) {
                    $isActive = false;
                }
                if ($contentType == 'course' && !($discount['courses'] ?? false)) {
                    $isActive = false;
                }
            }

            if ($isActive) {
                $activeDiscounts[] = [
                    'code' => $discount['code'],
                    'percentage' => $discount['percentage'],
                    'description' => "Get {$discount['percentage']}% off",
                    'valid_for_exams' => $discount['exams'] ?? false,
                    'valid_for_courses' => $discount['courses'] ?? false,
                ];
            }
        }

        \Log::info('Found active discounts:', ['count' => count($activeDiscounts), 'contentType' => $contentType]);
        return $activeDiscounts;
        
        } catch (\Exception $e) {
            \Log::error('Error getting active discounts:', ['error' => $e->getMessage()]);
            return [];
        }
    }
}