<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KelompokUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $sessionId,
        public readonly int $kelompokId,
        public readonly int $terisi,
        public readonly int $kuota,
        public readonly int $sisa,
        public readonly bool $isFull,
        public readonly string $timestamp,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("war.{$this->sessionId}")];
    }

    public function broadcastAs(): string
    {
        return 'KelompokUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->sessionId,
            'kelompok_id' => $this->kelompokId,
            'terisi' => $this->terisi,
            'kuota' => $this->kuota,
            'sisa' => $this->sisa,
            'is_full' => $this->isFull,
            'timestamp' => $this->timestamp,
        ];
    }
}
