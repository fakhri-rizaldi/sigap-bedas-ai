<?php

namespace App\Events;

use App\Models\Aduan;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AduanStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Aduan $aduan;
    public string $oldStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(Aduan $aduan, string $oldStatus = '')
    {
        $this->aduan = $aduan;
        $this->oldStatus = $oldStatus;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new Channel('aduans'),
            new Channel('aduan.' . $this->aduan->kode_tiket),
        ];

        if ($this->aduan->dinas_id) {
            $channels[] = new Channel('dinas.' . $this->aduan->dinas_id);
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'aduan.status_updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->aduan->id,
            'kode_tiket' => $this->aduan->kode_tiket,
            'status' => $this->aduan->status,
            'old_status' => $this->oldStatus,
            'catatan_petugas' => $this->aduan->catatan_petugas,
            'dinas_id' => $this->aduan->dinas_id,
            'updated_at' => $this->aduan->updated_at?->toIso8601String() ?? now()->toIso8601String(),
        ];
    }
}
