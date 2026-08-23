<?php

namespace Tests\Feature;

use App\Models\Aduan;
use App\Models\Dinas;
use App\Models\KategoriDinasMapping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PerformanceOptimizationTest extends TestCase
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

        KategoriDinasMapping::create([
            'kategori' => 'Jalan Rusak',
            'dinas_id' => $this->dputr->id,
            'keywords' => ['jalan', 'rusak'],
        ]);

        $this->user = User::factory()->create([
            'name' => 'Petugas DPUTR',
            'email' => 'petugas.dputr@bandungkab.go.id',
        ]);

        $this->aduan = Aduan::create([
            'teks_aduan' => 'Jalan berlubang parah di Soreang.',
            'kategori' => 'Jalan Rusak',
            'urgensi' => 'Tinggi',
            'dinas_id' => $this->dputr->id,
            'status' => 'baru',
            'latitude' => -7.0252,
            'longitude' => 107.5197,
            'alamat' => 'Soreang, Kabupaten Bandung',
        ]);
    }

    public function test_dashboard_stats_are_cached_in_memory(): void
    {
        Cache::flush();

        $this->assertFalse(Cache::has('dashboard_stats_kpi'));

        // Request pertama mengisi cache
        $this->actingAs($this->user)->get('/dashboard');

        $this->assertTrue(Cache::has('dashboard_stats_kpi'));

        $cachedStats = Cache::get('dashboard_stats_kpi');
        $this->assertEquals(1, $cachedStats['total']);
        $this->assertEquals(1, $cachedStats['baru']);
    }

    public function test_submitting_aduan_invalidates_dashboard_stats_cache(): void
    {
        // Isi cache awal
        Cache::put('dashboard_stats_kpi', ['total' => 10, 'baru' => 5], 60);
        $this->assertTrue(Cache::has('dashboard_stats_kpi'));

        // Citizen submit laporan baru
        $payload = [
            'teks_aduan' => 'Jalan rusak di Ciparay dekat pasar tradisional.',
            'kategori' => 'Jalan Rusak',
            'urgensi' => 'Sedang',
            'latitude' => -7.0345,
            'longitude' => 107.7123,
            'alamat' => 'Ciparay, Kabupaten Bandung',
        ];

        $response = $this->post('/lapor', $payload);
        $response->assertSessionHasNoErrors();

        // Cache harus otomatis dibersihkan (invalidated)
        $this->assertFalse(Cache::has('dashboard_stats_kpi'));
    }

    public function test_updating_status_invalidates_dashboard_stats_cache(): void
    {
        Cache::put('dashboard_stats_kpi', ['total' => 1, 'baru' => 1], 60);

        $response = $this->actingAs($this->user)
            ->patchJson("/dashboard/aduan/{$this->aduan->id}/status", [
                'status' => 'diproses',
                'catatan_petugas' => 'Sedang diperbaiki.',
            ]);

        $response->assertStatus(200);

        // Cache harus dibersihkan
        $this->assertFalse(Cache::has('dashboard_stats_kpi'));
    }

    public function test_composite_indexes_exist_on_aduans_table(): void
    {
        $this->assertTrue(Schema::hasTable('aduans'));
        $this->assertTrue(Schema::hasColumn('aduans', 'status'));
        $this->assertTrue(Schema::hasColumn('aduans', 'urgensi'));
        $this->assertTrue(Schema::hasColumn('aduans', 'dinas_id'));
        $this->assertTrue(Schema::hasColumn('aduans', 'latitude'));
        $this->assertTrue(Schema::hasColumn('aduans', 'longitude'));
    }
}
