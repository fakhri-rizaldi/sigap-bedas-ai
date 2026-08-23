<?php

namespace Tests\Feature;

use App\Models\Aduan;
use App\Models\Dinas;
use App\Models\KategoriDinasMapping;
use Database\Seeders\DinasSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AduanPublicSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DinasSeeder::class);
    }

    public function test_lapor_page_can_be_rendered(): void
    {
        $response = $this->get('/lapor');
        $response->assertStatus(200);
    }

    public function test_geocode_endpoint_works_with_mock(): void
    {
        Http::fake([
            'https://nominatim.openstreetmap.org/reverse*' => Http::response([
                'display_name' => 'Jl. Raya Soreang No. 12, Pamekaran, Soreang, Kab. Bandung',
                'address' => [
                    'road' => 'Jl. Raya Soreang',
                    'village' => 'Pamekaran',
                    'municipality' => 'Soreang',
                    'county' => 'Kabupaten Bandung',
                    'state' => 'Jawa Barat',
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/geocode?lat=-7.025&lng=107.519');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'jalan' => 'Jl. Raya Soreang',
                    'kabupaten_kota' => 'Kabupaten Bandung',
                ],
            ]);
    }

    public function test_citizen_can_submit_aduan_with_photo_and_auto_routes_to_dinas(): void
    {
        Storage::fake('public');

        $photo = UploadedFile::fake()->image('bukti_jalan_rusak.jpg', 600, 400);

        $payload = [
            'teks_aduan' => 'Jalan aspal di Raya Soreang amblas dan berlubang parah sering mencelakakan pengendara motor.',
            'kategori' => 'Jalan Rusak',
            'confidence_kategori' => 0.95,
            'urgensi' => 'Tinggi',
            'alasan_urgensi' => 'Lubang dalam di jalan raya utama.',
            'latitude' => -7.0252,
            'longitude' => 107.5197,
            'alamat' => 'Jl. Raya Soreang, Kab. Bandung',
            'foto' => $photo,
            'nama_pelapor' => 'Ahmad Warga',
            'kontak_pelapor' => '08123456789',
            'email_pelapor' => 'ahmad@example.com',
        ];

        $response = $this->post('/lapor', $payload);

        $dputr = Dinas::where('kode_dinas', 'DPUTR')->first();
        $this->assertNotNull($dputr);

        $this->assertDatabaseHas('aduans', [
            'kategori' => 'Jalan Rusak',
            'urgensi' => 'Tinggi',
            'dinas_id' => $dputr->id,
            'nama_pelapor' => 'Ahmad Warga',
            'email_pelapor' => 'ahmad@example.com',
        ]);

        $aduan = Aduan::where('nama_pelapor', 'Ahmad Warga')->first();
        $this->assertNotNull($aduan);
        $this->assertNotNull($aduan->foto_path);
        $this->assertStringStartsWith('BDS-', $aduan->kode_tiket);

        $response->assertRedirect(route('lapor.success', ['kodeTiket' => $aduan->kode_tiket]));

        // Cek halaman sukses
        $successResponse = $this->get(route('lapor.success', ['kodeTiket' => $aduan->kode_tiket]));
        $successResponse->assertStatus(200);
    }

    public function test_citizen_can_submit_aduan_with_base64_photo(): void
    {
        Storage::fake('public');

        // Sample 1x1 transparent GIF base64
        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $payload = [
            'teks_aduan' => 'Pohon tumbang menutup jalan raya Banjaran Soreang.',
            'kategori' => 'Jalan Rusak',
            'urgensi' => 'Darurat',
            'latitude' => -7.0425,
            'longitude' => 107.5878,
            'alamat' => 'Banjaran, Kabupaten Bandung',
            'foto' => $base64Image,
            'nama_pelapor' => 'Rina Base64',
        ];

        $response = $this->post('/lapor', $payload);
        $response->assertSessionHasNoErrors();

        $aduan = Aduan::where('nama_pelapor', 'Rina Base64')->first();
        $this->assertNotNull($aduan);
        $this->assertNotNull($aduan->foto_path);
        $this->assertStringContainsString('/storage/aduans/', $aduan->foto_path);
    }
}
