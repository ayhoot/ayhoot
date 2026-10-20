<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PaymentSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'authority',
        'user_id',
        'user_type',
        'content_type',
        'content_id',
        'amount',
        'original_amount',
        'discount_code',
        'discount_amount',
        'status',
        'payment_reference',
        'expires_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'original_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'expires_at' => 'datetime'
    ];

    /**
     * Create a new payment session
     */
    public static function createSession($authority, $data)
    {
        return self::create([
            'authority' => $authority,
            'user_id' => $data['user_id'],
            'user_type' => $data['user_type'],
            'content_type' => $data['content_type'],
            'content_id' => $data['content_id'],
            'amount' => $data['amount'],
            'original_amount' => $data['original_amount'],
            'discount_code' => $data['discount_code'],
            'discount_amount' => $data['discount_amount'],
            'status' => 'pending',
            'expires_at' => Carbon::now()->addHours(1) // 1 hour expiration
        ]);
    }

    /**
     * Get payment session by authority
     */
    public static function getByAuthority($authority)
    {
        return self::where('authority', $authority)
                   ->where('expires_at', '>', Carbon::now())
                   ->first();
    }

    /**
     * Mark session as completed
     */
    public function markCompleted($paymentReference)
    {
        $this->update([
            'status' => 'completed',
            'payment_reference' => $paymentReference
        ]);
    }

    /**
     * Mark session as failed
     */
    public function markFailed()
    {
        $this->update(['status' => 'failed']);
    }

    /**
     * Check if session is expired
     */
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if session is completed
     */
    public function isCompleted()
    {
        return $this->status == 'completed';
    }
}
