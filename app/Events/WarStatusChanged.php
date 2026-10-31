<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WarStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $sessionId,
        public readonly string $status,
        public readonly string $timestamp,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("war.{$this->sessionId}")];
    }

    public function broadcastAs(): string
    {
        return 'WarStatusChanged';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->sessionId,
            'status' => $this->status,
            'timestamp' => $this->timestamp,
        ];
    }
}
