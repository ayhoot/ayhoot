<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Clear all previous data for a clean slate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Plan::truncate();
        Feature::truncate();
        DB::table('plan_features')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. The complete data for all plans with comprehensive permissions
        $plansData = [
            [
                'plan' => [
                    'title' => 'Free', 
                    'price' => 0, 
                    'is_free' => true,
                    'description' => 'Basic plan for getting started'
                ],
                'features' => [
                    ['name' => 'Tutees per month', 'role' => 'tutees_per_month', 'value' => '20'],
                    ['name' => 'Participant Limit', 'role' => 'participant_limit', 'value' => '10'],
                    ['name' => 'Ayhoots (Exams)', 'role' => 'ayhoots', 'value' => '5'],
                    ['name' => 'Courses', 'role' => 'courses', 'value' => '0'], // No courses in free plan
                    ['name' => 'Groups', 'role' => 'groups', 'value' => '2'],
                    ['name' => 'Reports', 'role' => 'reports', 'value' => 'Basic'],
                    ['name' => 'Monetize', 'role' => 'monetize', 'value' => 'No'],
                    ['name' => 'Apply to Institute', 'role' => 'apply_to_institute', 'value' => '1'],
                    ['name' => 'Occupy Tutors', 'role' => 'occupy_tutors', 'value' => '0'],
                ]
            ],
            [
                'plan' => [
                    'title' => 'Bronze Ayhoot!', 
                    'price' => 390000,
                    'description' => 'Perfect for small educators and tutors'
                ],
                'features' => [
                    ['name' => 'Tutees per month', 'role' => 'tutees_per_month', 'value' => '50'],
                    ['name' => 'Participant Limit', 'role' => 'participant_limit', 'value' => '25'],
                    ['name' => 'Ayhoots (Exams)', 'role' => 'ayhoots', 'value' => '30'],
                    ['name' => 'Courses', 'role' => 'courses', 'value' => '5'],
                    ['name' => 'Groups', 'role' => 'groups', 'value' => '10'],
                    ['name' => 'Reports', 'role' => 'reports', 'value' => 'Advanced'],
                    ['name' => 'Monetize', 'role' => 'monetize', 'value' => 'Yes'],
                    ['name' => 'Apply to Institute', 'role' => 'apply_to_institute', 'value' => '2'],
                    ['name' => 'Occupy Tutors', 'role' => 'occupy_tutors', 'value' => '5'],
                ]
            ],
            [
                'plan' => [
                    'title' => 'Silver Ayhoot!', 
                    'price' => 670000, 
                    'is_popular' => true,
                    'description' => 'Most popular plan for growing educators'
                ],
                'features' => [
                    ['name' => 'Tutees per month', 'role' => 'tutees_per_month', 'value' => '150'],
                    ['name' => 'Participant Limit', 'role' => 'participant_limit', 'value' => '50'],
                    ['name' => 'Ayhoots (Exams)', 'role' => 'ayhoots', 'value' => '100'],
                    ['name' => 'Courses', 'role' => 'courses', 'value' => '25'],
                    ['name' => 'Groups', 'role' => 'groups', 'value' => '25'],
                    ['name' => 'Reports', 'role' => 'reports', 'value' => 'Ultimate'],
                    ['name' => 'Monetize', 'role' => 'monetize', 'value' => 'Yes'],
                    ['name' => 'Apply to Institute', 'role' => 'apply_to_institute', 'value' => '3'],
                    ['name' => 'Occupy Tutors', 'role' => 'occupy_tutors', 'value' => '15'],
                ]
            ],
            [
                'plan' => [
                    'title' => 'Golden Ayhoot!', 
                    'price' => 950000,
                    'description' => 'Premium plan for large institutions'
                ],
                'features' => [
                    ['name' => 'Tutees per month', 'role' => 'tutees_per_month', 'value' => '500'],
                    ['name' => 'Participant Limit', 'role' => 'participant_limit', 'value' => '200'],
                    ['name' => 'Ayhoots (Exams)', 'role' => 'ayhoots', 'value' => '500'],
                    ['name' => 'Courses', 'role' => 'courses', 'value' => '100'],
                    ['name' => 'Groups', 'role' => 'groups', 'value' => '100'],
                    ['name' => 'Reports', 'role' => 'reports', 'value' => 'Ultimate Pro'],
                    ['name' => 'Monetize', 'role' => 'monetize', 'value' => 'Yes'],
                    ['name' => 'Apply to Institute', 'role' => 'apply_to_institute', 'value' => '5'],
                    ['name' => 'Occupy Tutors', 'role' => 'occupy_tutors', 'value' => '50'],
                ]
            ],
            [
                'plan' => [
                    'title' => 'Enterprise', 
                    'price' => 1500000,
                    'description' => 'Unlimited plan for large organizations'
                ],
                'features' => [
                    ['name' => 'Tutees per month', 'role' => 'tutees_per_month', 'value' => '9999'],
                    ['name' => 'Participant Limit', 'role' => 'participant_limit', 'value' => '9999'],
                    ['name' => 'Ayhoots (Exams)', 'role' => 'ayhoots', 'value' => '9999'],
                    ['name' => 'Courses', 'role' => 'courses', 'value' => '9999'],
                    ['name' => 'Groups', 'role' => 'groups', 'value' => '9999'],
                    ['name' => 'Reports', 'role' => 'reports', 'value' => 'Enterprise'],
                    ['name' => 'Monetize', 'role' => 'monetize', 'value' => 'Yes'],
                    ['name' => 'Apply to Institute', 'role' => 'apply_to_institute', 'value' => '10'],
                    ['name' => 'Occupy Tutors', 'role' => 'occupy_tutors', 'value' => '9999'],
                ]
            ]
        ];

        // 3. Loop through all data, process it, and create records
        foreach ($plansData as $data) {
            // Create the Plan
            $plan = Plan::create($data['plan']);

            $featureIds = [];

            // Loop through each feature for the current plan
            foreach ($data['features'] as $featureData) {
                $processedTitle = '';
                $processedValue = 0;

                // Check if the value is a number (e.g., "150") or text (e.g., "Basic")
                if (is_numeric($featureData['value'])) {
                    // It's a number. Create title like "150 Ayhoots"
                    $processedTitle = $featureData['value'] . ' ' . $featureData['name'];
                    // The value for the DB is the integer itself
                    $processedValue = (int)$featureData['value'];
                } else {
                    // It's text. Create title like "Reports: Basic"
                    $processedTitle = $featureData['name'] . ': ' . $featureData['value'];
                    // The value for the DB is 1 for "Yes" and 0 for "No"
                    $processedValue = strtolower($featureData['value']) === 'yes' ? 1 : 
                                    (strtolower($featureData['value']) === 'no' ? 0 : 1);
                }

                // Create the feature with the processed title and value
                $feature = Feature::create([
                    'title' => $processedTitle,
                    'role'  => $featureData['role'],
                    'value' => $processedValue, // This is now always an integer
                ]);

                // Collect the ID of the new feature
                $featureIds[] = $feature->id;
            }

            // Attach all the new features to the plan
            $plan->features()->attach($featureIds);
        }

        // Assign default free plan to existing users without plans
        $this->assignDefaultPlans();
    }

    /**
     * Assign default free plan to existing users
     */
    private function assignDefaultPlans()
    {
        $freePlan = Plan::where('is_free', true)->first();
        
        if ($freePlan) {
            // Assign to teachers without plans
            \App\Models\Teacher::whereNull('plan_id')->update(['plan_id' => $freePlan->id]);
            
            // Assign to institutes without plans
            \App\Models\Institute::whereNull('plan_id')->update(['plan_id' => $freePlan->id]);
        }
    }
}