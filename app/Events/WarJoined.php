<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WarJoined implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $sessionId,
        public readonly int $kelompokId,
        public readonly int $pesertaId,
        public readonly int $memberCount,
        public readonly string $timestamp,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("war.{$this->sessionId}")];
    }

    public function broadcastAs(): string
    {
        return 'WarJoined';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->sessionId,
            'kelompok_id' => $this->kelompokId,
            'peserta_id' => $this->pesertaId,
            'member_count' => $this->memberCount,
            'timestamp' => $this->timestamp,
        ];
    }
}
