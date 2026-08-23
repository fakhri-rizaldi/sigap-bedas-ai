<?php

namespace Tests\Feature;

use App\Events\AduanCreated;
use App\Events\AduanStatusUpdated;
use App\Models\Aduan;
use App\Models\Dinas;
use App\Models\KategoriDinasMapping;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BroadcastingEventTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed dinas dan kategori mapping
        $dputr = Dinas::create([
            'nama_dinas' => 'Dinas Pekerjaan Umum dan Tata Ruang',
            'kode_dinas' => 'DPUTR',
            'deskripsi' => 'Penanganan jalan & jembatan',
        ]);

        KategoriDinasMapping::create([
            'kategori' => 'Jalan Rusak',
            'dinas_id' => $dputr->id,
            'keywords' => ['jalan', 'rusak', 'lubang', 'aspal'],
        ]);
    }

    public function test_aduan_created_event_is_dispatched_on_public_submission(): void
    {
        Event::fake([AduanCreated::class]);

        $payload = [
            'teks_aduan' => 'Jalan aspal di dekat Soreang berlubang parah sering mencelakakan pengendara.',
            'kategori' => 'Jalan Rusak',
            'confidence_kategori' => 0.95,
            'urgensi' => 'Tinggi',
            'alasan_urgensi' => 'Jalan berlubang besar.',
            'latitude' => -7.0252,
            'longitude' => 107.5197,
            'alamat' => 'Jl. Raya Soreang, Kab. Bandung',
            'nama_pelapor' => 'Budi Warga',
            'kontak_pelapor' => '08123456789',
        ];

        $response = $this->post('/lapor', $payload);
        $response->assertSessionHasNoErrors();

        Event::assertDispatched(AduanCreated::class, function (AduanCreated $event) {
            return $event->aduan->kategori === 'Jalan Rusak'
                && $event->aduan->nama_pelapor === 'Budi Warga'
                && $event->aduan->dinas_id !== null;
        });
    }

    public function test_aduan_created_broadcast_channels_and_payload(): void
    {
        $dputr = Dinas::where('kode_dinas', 'DPUTR')->first();

        $aduan = Aduan::create([
            'teks_aduan' => 'Pohon tumbang menghalangi jalan raya Soreang.',
            'kategori' => 'Jalan Rusak',
            'confidence_kategori' => 0.90,
            'urgensi' => 'Darurat',
            'alasan_urgensi' => 'Akses jalan tertutup total.',
            'dinas_id' => $dputr->id,
            'status' => 'baru',
            'latitude' => -7.0252,
            'longitude' => 107.5197,
            'alamat' => 'Jl. Raya Soreang',
            'nama_pelapor' => 'Siti',
            'kontak_pelapor' => '08129876543',
        ]);

        $event = new AduanCreated($aduan);

        // Verifikasi nama event
        $this->assertEquals('aduan.created', $event->broadcastAs());

        // Verifikasi channel broadcast
        $channels = $event->broadcastOn();
        $channelNames = array_map(fn($c) => $c->name, $channels);

        $this->assertContains('aduans', $channelNames);
        $this->assertContains('dinas.' . $dputr->id, $channelNames);

        // Verifikasi payload broadcast
        $payload = $event->broadcastWith();
        $this->assertEquals($aduan->kode_tiket, $payload['kode_tiket']);
        $this->assertEquals('Jalan Rusak', $payload['kategori']);
        $this->assertEquals('Darurat', $payload['urgensi']);
        $this->assertEquals($dputr->id, $payload['dinas_id']);
        $this->assertEquals('baru', $payload['status']);
    }

    public function test_aduan_status_updated_broadcast_channels_and_payload(): void
    {
        $dputr = Dinas::where('kode_dinas', 'DPUTR')->first();

        $aduan = Aduan::create([
            'teks_aduan' => 'Jalan berlubang besar di Katapang.',
            'kategori' => 'Jalan Rusak',
            'urgensi' => 'Sedang',
            'dinas_id' => $dputr->id,
            'status' => 'baru',
            'latitude' => -7.0055,
            'longitude' => 107.5685,
            'alamat' => 'Kecamatan Katapang, Kabupaten Bandung',
        ]);

        // Simulasi update status oleh petugas dinas
        $aduan->update([
            'status' => 'diproses',
            'catatan_petugas' => 'Tim penambalan aspal DPUTR sedang menuju lokasi.',
        ]);

        $event = new AduanStatusUpdated($aduan, 'baru');

        // Verifikasi nama event
        $this->assertEquals('aduan.status_updated', $event->broadcastAs());

        // Verifikasi channel
        $channels = $event->broadcastOn();
        $channelNames = array_map(fn($c) => $c->name, $channels);

        $this->assertContains('aduans', $channelNames);
        $this->assertContains('aduan.' . $aduan->kode_tiket, $channelNames);
        $this->assertContains('dinas.' . $dputr->id, $channelNames);

        // Verifikasi payload
        $payload = $event->broadcastWith();
        $this->assertEquals($aduan->kode_tiket, $payload['kode_tiket']);
        $this->assertEquals('diproses', $payload['status']);
        $this->assertEquals('baru', $payload['old_status']);
        $this->assertEquals('Tim penambalan aspal DPUTR sedang menuju lokasi.', $payload['catatan_petugas']);
    }
}
