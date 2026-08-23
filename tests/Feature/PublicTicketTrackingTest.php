<?php

namespace Tests\Feature;

use App\Models\Aduan;
use App\Models\Dinas;
use App\Models\KategoriDinasMapping;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicTicketTrackingTest extends TestCase
{
    use RefreshDatabase;

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

        KategoriDinasMapping::create([
            'kategori' => 'Jalan Rusak',
            'dinas_id' => $this->dputr->id,
            'keywords' => ['jalan', 'rusak'],
        ]);

        $this->aduan = Aduan::create([
            'kode_tiket' => 'BDS-20260823-9A7B',
            'teks_aduan' => 'Jalan raya Soreang berlubang parah.',
            'kategori' => 'Jalan Rusak',
            'urgensi' => 'Tinggi',
            'dinas_id' => $this->dputr->id,
            'status' => 'diproses',
            'catatan_petugas' => 'Tim teknis sedang melakukan pengaspalan ulang.',
            'latitude' => -7.0252,
            'longitude' => 107.5197,
            'alamat' => 'Soreang, Kabupaten Bandung',
            'nama_pelapor' => 'Budi Santoso',
        ]);
    }

    public function test_citizen_can_view_tracking_search_page_without_authentication(): void
    {
        $response = $this->get('/lapor/status');
        $response->assertStatus(200);
    }

    public function test_citizen_can_view_ticket_tracking_detail_by_code(): void
    {
        $response = $this->get('/lapor/status/' . $this->aduan->kode_tiket);
        $response->assertStatus(200);
        $response->assertSee($this->aduan->kode_tiket);
        $response->assertSee('Dinas Pekerjaan Umum dan Tata Ruang');
    }

    public function test_search_by_query_parameter(): void
    {
        $response = $this->get('/lapor/status?kode=' . $this->aduan->kode_tiket);
        $response->assertStatus(200);
        $response->assertSee($this->aduan->kode_tiket);
    }

    public function test_case_insensitive_ticket_code_search(): void
    {
        $response = $this->get('/lapor/status/bds-20260823-9a7b');
        $response->assertStatus(200);
        $response->assertSee($this->aduan->kode_tiket);
    }

    public function test_non_existent_ticket_search_returns_clean_searched_state(): void
    {
        $response = $this->get('/lapor/status/BDS-99999999-XXXX');
        $response->assertStatus(200);
        $response->assertSee('BDS-99999999-XXXX');
    }
}
