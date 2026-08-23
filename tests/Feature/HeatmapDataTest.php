<?php

namespace Tests\Feature;

use App\Models\Aduan;
use App\Models\Dinas;
use App\Models\KategoriDinasMapping;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeatmapDataTest extends TestCase
{
    use RefreshDatabase;

    protected Dinas $dputr;
    protected Dinas $dlh;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dputr = Dinas::create([
            'nama_dinas' => 'Dinas Pekerjaan Umum dan Tata Ruang',
            'kode_dinas' => 'DPUTR',
            'deskripsi' => 'Penanganan jalan & jembatan',
        ]);

        $this->dlh = Dinas::create([
            'nama_dinas' => 'Dinas Lingkungan Hidup',
            'kode_dinas' => 'DLH',
            'deskripsi' => 'Pengelolaan sampah & drainase',
        ]);

        KategoriDinasMapping::create([
            'kategori' => 'Jalan Rusak',
            'dinas_id' => $this->dputr->id,
            'keywords' => ['jalan', 'rusak'],
        ]);

        KategoriDinasMapping::create([
            'kategori' => 'Sampah & Lingkungan',
            'dinas_id' => $this->dlh->id,
            'keywords' => ['sampah', 'bau'],
        ]);

        // 1. Aduan Darurat (Soreang)
        Aduan::create([
            'teks_aduan' => 'Jalan amblas di Soreang',
            'kategori' => 'Jalan Rusak',
            'urgensi' => 'Darurat',
            'dinas_id' => $this->dputr->id,
            'status' => 'baru',
            'latitude' => -7.0252,
            'longitude' => 107.5197,
            'alamat' => 'Soreang, Kabupaten Bandung',
        ]);

        // 2. Aduan Tinggi (Dayeuhkolot)
        Aduan::create([
            'teks_aduan' => 'Sampah menumpuk di Dayeuhkolot',
            'kategori' => 'Sampah & Lingkungan',
            'urgensi' => 'Tinggi',
            'dinas_id' => $this->dlh->id,
            'status' => 'diproses',
            'latitude' => -6.9839,
            'longitude' => 107.6253,
            'alamat' => 'Dayeuhkolot, Kabupaten Bandung',
        ]);

        // 3. Aduan Sedang (Banjaran)
        Aduan::create([
            'teks_aduan' => 'Lampu PJU mati di Banjaran',
            'kategori' => 'Jalan Rusak',
            'urgensi' => 'Sedang',
            'dinas_id' => $this->dputr->id,
            'status' => 'selesai',
            'latitude' => -7.0425,
            'longitude' => 107.5878,
            'alamat' => 'Banjaran, Kabupaten Bandung',
        ]);
    }

    public function test_heatmap_endpoint_returns_valid_data_structure_and_weights(): void
    {
        $response = $this->getJson('/api/aduan/heatmap-data');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'count',
                'data' => [
                    '*' => [
                        'id',
                        'kode_tiket',
                        'lat',
                        'lng',
                        'weight',
                        'kategori',
                        'urgensi',
                        'status',
                        'alamat',
                        'teks_aduan',
                        'foto_path',
                        'dinas_nama',
                    ]
                ]
            ]);

        $data = $response->json('data');
        $this->assertCount(3, $data);

        // Validasi pembobotan urgensi
        $daruratPoint = collect($data)->firstWhere('urgensi', 'Darurat');
        $this->assertNotNull($daruratPoint);
        $this->assertEquals(1.0, $daruratPoint['weight']);

        $tinggiPoint = collect($data)->firstWhere('urgensi', 'Tinggi');
        $this->assertNotNull($tinggiPoint);
        $this->assertEquals(0.8, $tinggiPoint['weight']);

        $sedangPoint = collect($data)->firstWhere('urgensi', 'Sedang');
        $this->assertNotNull($sedangPoint);
        $this->assertEquals(0.5, $sedangPoint['weight']);
    }

    public function test_heatmap_endpoint_filters_correctly(): void
    {
        // Filter by status=baru
        $res1 = $this->getJson('/api/aduan/heatmap-data?status=baru');
        $res1->assertStatus(200);
        $this->assertEquals(1, $res1->json('count'));
        $this->assertEquals('Darurat', $res1->json('data.0.urgensi'));

        // Filter by dinas_id
        $res2 = $this->getJson('/api/aduan/heatmap-data?dinas_id=' . $this->dlh->id);
        $res2->assertStatus(200);
        $this->assertEquals(1, $res2->json('count'));
        $this->assertEquals('Sampah & Lingkungan', $res2->json('data.0.kategori'));

        // Filter by kecamatan (Banjaran)
        $res3 = $this->getJson('/api/aduan/heatmap-data?kecamatan=Banjaran');
        $res3->assertStatus(200);
        $this->assertEquals(1, $res3->json('count'));
        $this->assertEquals('Sedang', $res3->json('data.0.urgensi'));
    }
}
