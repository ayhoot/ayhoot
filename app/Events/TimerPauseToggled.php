<?php

namespace App\Events;

use App\Models\LiveSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TimerPauseToggled implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $session;
    public $paused;
    public $remainingSeconds;

    public function __construct(LiveSession $session, bool $paused, int $remainingSeconds = 0)
    {
        $this->session = $session;
        $this->paused = $paused;
        $this->remainingSeconds = $remainingSeconds;
    }

    public function broadcastOn()
    {
        return new Channel('live-session.' . $this->session->pin);
    }

    public function broadcastAs()
    {
        return 'timer.paused';
    }

    public function broadcastWith()
    {
        return [
            'paused' => $this->paused,
            'remaining_seconds' => $this->remainingSeconds,
            'message' => $this->paused ? 'Session timer paused' : 'Session timer resumed',
        ];
    }
}
