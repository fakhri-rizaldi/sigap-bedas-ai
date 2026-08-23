<?php

namespace Tests\Feature;

use App\Events\AduanStatusUpdated;
use App\Models\Aduan;
use App\Models\Dinas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Dinas $dputr;
    protected Aduan $aduan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dputr = Dinas::create([
            'nama_dinas' => 'Dinas Pekerjaan Umum dan Tata Ruang',
            'kode_dinas' => 'DPUTR',
            'deskripsi' => 'Penanganan jalan & jembatan',
        ]);

        $this->user = User::factory()->create([
            'name' => 'Petugas DPUTR',
            'email' => 'petugas.dputr@bandungkab.go.id',
        ]);

        $this->aduan = Aduan::create([
            'teks_aduan' => 'Jalan berlubang parah di Soreang membahayakan warga.',
            'kategori' => 'Jalan Rusak',
            'confidence_kategori' => 0.95,
            'urgensi' => 'Tinggi',
            'alasan_urgensi' => 'Jalan berlubang besar.',
            'dinas_id' => $this->dputr->id,
            'status' => 'baru',
            'latitude' => -7.0252,
            'longitude' => 107.5197,
            'alamat' => 'Kecamatan Soreang, Kabupaten Bandung',
            'nama_pelapor' => 'Budi Santoso',
            'kontak_pelapor' => '08123456789',
        ]);
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_staff_can_view_dashboard_with_tickets_and_stats(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn($page) => $page
            ->component('Dashboard')
            ->has('aduans.data', 1)
            ->has('stats')
            ->has('dinasList')
            ->has('kecamatanList')
        );
    }

    public function test_staff_can_update_ticket_status_and_triggers_broadcast(): void
    {
        Event::fake([AduanStatusUpdated::class]);

        $response = $this->actingAs($this->user)
            ->patchJson("/dashboard/aduan/{$this->aduan->id}/status", [
                'status' => 'diproses',
                'catatan_petugas' => 'Petugas DPUTR sedang melakukan penambalan aspal.',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);

        $this->assertDatabaseHas('aduans', [
            'id' => $this->aduan->id,
            'status' => 'diproses',
            'catatan_petugas' => 'Petugas DPUTR sedang melakukan penambalan aspal.',
        ]);

        Event::assertDispatched(AduanStatusUpdated::class, function (AduanStatusUpdated $event) {
            return $event->aduan->id === $this->aduan->id
                && $event->aduan->status === 'diproses'
                && $event->oldStatus === 'baru';
        });
    }

    public function test_dashboard_filtering_by_status_and_search(): void
    {
        // Aduan kedua dengan status selesai
        Aduan::create([
            'teks_aduan' => 'Sampah menumpuk di pinggir kali Dayeuhkolot.',
            'kategori' => 'Sampah & Lingkungan',
            'urgensi' => 'Sedang',
            'status' => 'selesai',
            'latitude' => -6.9839,
            'longitude' => 107.6253,
            'alamat' => 'Dayeuhkolot, Kabupaten Bandung',
            'nama_pelapor' => 'Siti',
        ]);

        // Filter status = baru
        $responseBaru = $this->actingAs($this->user)->get('/dashboard?status=baru');
        $responseBaru->assertStatus(200);
        $responseBaru->assertInertia(fn($page) => $page
            ->component('Dashboard')
            ->has('aduans.data', 1)
        );

        // Filter search = Dayeuhkolot
        $responseSearch = $this->actingAs($this->user)->get('/dashboard?search=Dayeuhkolot');
        $responseSearch->assertStatus(200);
        $responseSearch->assertInertia(fn($page) => $page
            ->component('Dashboard')
            ->has('aduans.data', 1)
        );
    }
}