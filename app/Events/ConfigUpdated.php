<?php

namespace App\Events;

use App\Models\LiveSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConfigUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $session;
    public $config;

    public function __construct(LiveSession $session, array $config = [])
    {
        $this->session = $session;
        $this->config = $config;
    }

    public function broadcastOn()
    {
        return new Channel('live-session.' . $this->session->pin);
    }

    public function broadcastAs()
    {
        return 'config.updated';
    }

    public function broadcastWith()
    {
        return [
            'config' => $this->config,
            'auto_advance' => $this->config['auto_advance'] ?? false,
            'teacher_paced' => $this->config['teacher_paced'] ?? true,
            'show_results' => $this->config['show_results'] ?? true,
            'message' => 'Session configuration updated',
        ];
    }
}
