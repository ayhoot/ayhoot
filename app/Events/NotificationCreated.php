<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    public function broadcastOn()
    {
        // notifiable_type is the recipient model (App\Models\Teacher|Student|Institute|Admin).
        $type = strtolower(class_basename((string) $this->notification->notifiable_type));
        return new PrivateChannel("notifications.{$type}.{$this->notification->notifiable_id}");
    }

    public function broadcastAs()
    {
        return 'notification.created';
    }

    public function broadcastWith()
    {
        return [
            'notification' => [
                'id' => $this->notification->id,
                'type' => $this->notification->type,
                'message' => $this->notification->message,
                'data' => $this->notification->data,
                'read_at' => $this->notification->read_at,
                'created_at' => optional($this->notification->created_at)->toISOString(),
            ],
        ];
    }
}
