<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $rootId;
    public $action;

    /**
     * @param int|string $rootId The id of the root (parent) ticket the conversation belongs to.
     * @param string $action 'created' | 'reply' | 'updated'
     */
    public function __construct($rootId, $action = 'updated')
    {
        $this->rootId = $rootId;
        $this->action = $action;
    }

    public function broadcastOn()
    {
        return new PrivateChannel("ticket.{$this->rootId}");
    }

    public function broadcastAs()
    {
        return 'ticket.updated';
    }

    public function broadcastWith()
    {
        return [
            'ticket_id' => $this->rootId,
            'action' => $this->action,
        ];
    }
}
