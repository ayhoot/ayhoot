<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'message', 'status', 'parent_id', 'sender_type', 'sender_id', 'admin_id'];


    protected $with = ['sender'];

    protected static function booted()
    {
        // Broadcast ticket activity to the conversation's private channel so anyone
        // viewing the ticket (owner or admin) sees new replies / status changes live.
        static::created(function (self $ticket) {
            $root = $ticket->parent_id ?: $ticket->id;
            self::broadcastTicketActivity($root, $ticket->parent_id ? 'reply' : 'created');
        });
        static::updated(function (self $ticket) {
            $root = $ticket->parent_id ?: $ticket->id;
            self::broadcastTicketActivity($root, 'updated');
        });
    }

    protected static function broadcastTicketActivity($rootId, $action)
    {
        try {
            broadcast(new \App\Events\TicketUpdated($rootId, $action));
        } catch (\Throwable $e) {
            \Log::warning('TicketUpdated broadcast failed: ' . $e->getMessage());
        }
    }

    public function parent()
    {
        return $this->belongsTo(Ticket::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Ticket::class, 'parent_id');
    }

    // Ticket creator
    public function sender()
    {
        return $this->morphTo();
    }

    // Admin assigned to the ticket
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function tickets()
    {
        return $this->morphMany(Ticket::class, 'sender');
    }
}
