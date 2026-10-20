<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

class Notification extends Model
{
    use HasFactory, Notifiable;
    protected $fillable = [
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'message',
        'read_at',
        'locale',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        // Broadcast every new notification to its recipient's private channel so
        // all panels receive it live over the self-hosted websocket. Covers every
        // creation path (NotificationService, direct create, notifications()->create).
        static::created(function (self $notification) {
            if ($notification->notifiable_type && $notification->notifiable_id) {
                try {
                    broadcast(new \App\Events\NotificationCreated($notification));
                } catch (\Throwable $e) {
                    \Log::warning('NotificationCreated broadcast failed: ' . $e->getMessage());
                }
            }
        });
    }

    public function notifiable()
    {
        return $this->morphTo();
    }

    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->read_at = now();
            return $this->save();
        }
        return true;
    }

    public function markAsUnread()
    {
        $this->read_at = null;
        return $this->save();
    }

    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }


    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    public function scopeLocale($query, $locale)
    {
        return $query->where('locale', $locale);
    }
}