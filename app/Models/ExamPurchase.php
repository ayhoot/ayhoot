<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'purchaser_id',
        'purchaser_type',
        'invoice_id',
        'amount_paid',
        'original_amount',
        'discount_code',
        'discount_amount',
        'payment_method',
        'payment_status',
        'payment_reference',
        'purchased_at'
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'original_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'purchased_at' => 'datetime'
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function purchaser()
    {
        return $this->morphTo();
    }

    public function scopeForUser($query, $userId, $userType)
    {
        return $query->where('purchaser_id', $userId)
                    ->where('purchaser_type', $userType);
    }
}