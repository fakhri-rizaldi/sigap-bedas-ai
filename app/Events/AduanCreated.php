<?php

namespace App\Events;

use App\Models\Aduan;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AduanCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Aduan $aduan;

    /**
     * Create a new event instance.
     */
    public function __construct(Aduan $aduan)
    {
        $this->aduan = $aduan;
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
        return 'aduan.created';
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
            'teks_aduan' => $this->aduan->teks_aduan,
            'kategori' => $this->aduan->kategori,
            'confidence_kategori' => $this->aduan->confidence_kategori,
            'urgensi' => $this->aduan->urgensi,
            'alasan_urgensi' => $this->aduan->alasan_urgensi,
            'dinas_id' => $this->aduan->dinas_id,
            'dinas_nama' => $this->aduan->dinas?->nama_dinas,
            'status' => $this->aduan->status,
            'latitude' => $this->aduan->latitude,
            'longitude' => $this->aduan->longitude,
            'alamat' => $this->aduan->alamat,
            'foto_path' => $this->aduan->foto_path,
            'nama_pelapor' => $this->aduan->nama_pelapor,
            'sumber_klasifikasi' => $this->aduan->sumber_klasifikasi,
            'created_at' => $this->aduan->created_at?->toIso8601String() ?? now()->toIso8601String(),
        ];
    }
}
