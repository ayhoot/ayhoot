<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;
use Carbon\Carbon;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update existing discount codes or create if not exists
        Setting::updateOrCreate(
            ['key' => 'discount_codes'],
            [
                'value' => 'Active discount codes for the platform',
                'description' => 'Discount codes that can be applied during checkout',
                'discount_settings' => [
                    [
                        'code' => 'SAVE10',
                        'percentage' => 10,
                        'startDate' => Carbon::now()->subDays(7)->format('Y-m-d'),
                        'endDate' => Carbon::now()->addDays(30)->format('Y-m-d'),
                        'exams' => true,
                        'courses' => true,
                    ],
                    [
                        'code' => 'EXAM20',
                        'percentage' => 20,
                        'startDate' => Carbon::now()->subDays(3)->format('Y-m-d'),
                        'endDate' => Carbon::now()->addDays(15)->format('Y-m-d'),
                        'exams' => true,
                        'courses' => false,
                    ],
                    [
                        'code' => 'COURSE15',
                        'percentage' => 15,
                        'startDate' => Carbon::now()->subDays(1)->format('Y-m-d'),
                        'endDate' => Carbon::now()->addDays(45)->format('Y-m-d'),
                        'exams' => false,
                        'courses' => true,
                    ],
                ],
                'is_active' => true,
            ]
        );

        // Create payment settings
        Setting::updateOrCreate(
            ['key' => 'payment_config'],
            [
                'value' => 'Payment gateway configuration',
                'description' => 'Settings for payment processing',
                'payment_settings' => [
                    'gateway' => 'zarinpal',
                    'currency' => 'IRR',
                    'tax_rate' => 0.0,
                    'processing_fee' => 0.0,
                ],
                'is_active' => true,
            ]
        );

        // Create general settings
        Setting::updateOrCreate(
            ['key' => 'general_config'],
            [
                'value' => 'General platform settings',
                'description' => 'General configuration for the platform',
                'general_settings' => [
                    'platform_name' => 'Ayhoot',
                    'support_email' => 'support@ayhoot.com',
                    'max_discount_percentage' => 50,
                    'min_purchase_amount' => 1000,
                ],
                'is_active' => true,
            ]
        );
    }
}