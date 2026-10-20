<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\ExamPurchase;
use App\Models\CoursePurchase;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing paid invoices to the new purchase system
        $this->migrateExamPurchases();
        $this->migrateCoursePurchases();
    }

    private function migrateExamPurchases()
    {
        $paidExamInvoices = Invoice::where('status', 'paid')
            ->whereNotNull('exam_id')
            ->whereNotNull('teacher_id')
            ->orWhere(function($query) {
                $query->where('status', 'paid')
                      ->whereNotNull('exam_id')
                      ->whereNotNull('institute_id');
            })
            ->get();

        foreach ($paidExamInvoices as $invoice) {
            $purchaserType = $invoice->teacher_id ? 'teacher' : 'institute';
            $purchaserId = $invoice->teacher_id ?: $invoice->institute_id;

            // Check if purchase record already exists
            $existingPurchase = ExamPurchase::where('exam_id', $invoice->exam_id)
                ->where('purchaser_id', $purchaserId)
                ->where('purchaser_type', $purchaserType)
                ->first();

            if (!$existingPurchase) {
                ExamPurchase::create([
                    'exam_id' => $invoice->exam_id,
                    'purchaser_id' => $purchaserId,
                    'purchaser_type' => $purchaserType,
                    'invoice_id' => $invoice->id,
                    'purchase_price' => $invoice->amount,
                    'purchased_at' => $invoice->updated_at
                ]);
            }
        }
    }

    private function migrateCoursePurchases()
    {
        $paidCourseInvoices = Invoice::where('status', 'paid')
            ->whereNotNull('course_id')
            ->whereNotNull('teacher_id')
            ->orWhere(function($query) {
                $query->where('status', 'paid')
                      ->whereNotNull('course_id')
                      ->whereNotNull('institute_id');
            })
            ->get();

        foreach ($paidCourseInvoices as $invoice) {
            $purchaserType = $invoice->teacher_id ? 'teacher' : 'institute';
            $purchaserId = $invoice->teacher_id ?: $invoice->institute_id;

            // Check if purchase record already exists
            $existingPurchase = CoursePurchase::where('course_id', $invoice->course_id)
                ->where('purchaser_id', $purchaserId)
                ->where('purchaser_type', $purchaserType)
                ->first();

            if (!$existingPurchase) {
                CoursePurchase::create([
                    'course_id' => $invoice->course_id,
                    'purchaser_id' => $purchaserId,
                    'purchaser_type' => $purchaserType,
                    'invoice_id' => $invoice->id,
                    'purchase_price' => $invoice->amount,
                    'purchased_at' => $invoice->updated_at
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove migrated purchase records
        ExamPurchase::truncate();
        CoursePurchase::truncate();
    }
};