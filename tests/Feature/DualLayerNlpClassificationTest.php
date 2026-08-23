<?php

namespace Tests\Feature;

use App\Jobs\ValidasiKlasifikasiJob;
use App\Models\Aduan;
use App\Models\Dinas;
use App\Models\KategoriDinasMapping;
use App\Models\User;
use App\Services\NlpValidationService;
use Database\Seeders\DinasSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DualLayerNlpClassificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Dinas $dputr;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DinasSeeder::class);

        $this->dputr = Dinas::where('kode_dinas', 'DPUTR')->first();

        $this->admin = User::factory()->create([
            'email' => 'admin@bandungkab.go.id',
        ]);
    }

    public function test_gemini_and_local_nlp_agreement_sets_perlu_review_false(): void
    {
        // Mock FastAPI NLP microservice
        Http::fake([
            '*/predict' => Http::response([
                'status' => 'success',
                'kategori' => 'Jalan Rusak',
                'confidence' => 0.95,
                'model' => 'local_nlp_svm',
            ], 200),
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'kategori' => 'Jalan Rusak',
                                        'confidence' => 0.95,
                                        'urgensi' => 'Tinggi',
                                        'alasan' => 'Aspal jalan berlubang',
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $payload = [
            'teks_aduan' => 'Jalan raya Soreang km 15 berlubang parah sering bikin motor jatuh.',
            'latitude' => -7.0252,
            'longitude' => 107.5197,
            'alamat' => 'Soreang, Kabupaten Bandung',
        ];

        $response = $this->post('/lapor', $payload);
        $response->assertSessionHasNoErrors();

        $aduan = Aduan::latest()->first();
        $this->assertNotNull($aduan);
        $this->assertEquals('Jalan Rusak', $aduan->kategori);
        $this->assertEquals('Jalan Rusak', $aduan->kategori_model_lokal);
        $this->assertFalse((bool) $aduan->perlu_review);
    }

    public function test_discrepancy_between_models_sets_perlu_review_true(): void
    {
        // Gemini predicts 'Jalan Rusak', but Local Model predicts 'Sampah/Banjir' (Ambiguity Discrepancy)
        Http::fake([
            '*/predict' => Http::response([
                'status' => 'success',
                'kategori' => 'Sampah/Banjir',
                'confidence' => 0.88,
                'model' => 'local_nlp_svm',
            ], 200),
        ]);

        $payload = [
            'teks_aduan' => 'Jalanan di Dayeuhkolot rusak tertutup genangan sampah dan lumpur.',
            'kategori' => 'Jalan Rusak',
            'confidence_kategori' => 0.90,
            'urgensi' => 'Tinggi',
            'latitude' => -6.9856,
            'longitude' => 107.6258,
            'alamat' => 'Dayeuhkolot, Kabupaten Bandung',
        ];

        $response = $this->post('/lapor', $payload);
        $response->assertSessionHasNoErrors();

        $aduan = Aduan::latest()->first();
        $this->assertNotNull($aduan);
        $this->assertEquals('Jalan Rusak', $aduan->kategori);
        $this->assertEquals('Sampah/Banjir', $aduan->kategori_model_lokal);
        $this->assertTrue((bool) $aduan->perlu_review, 'Tiket harus berstatus perlu_review = true saat ada ketidaksesuaian kategori.');
    }

    public function test_local_nlp_service_fallback_when_microservice_offline(): void
    {
        // Simulasikan FastAPI microservice connection failure (cURL error / timeout)
        Http::fake([
            '*/predict' => Http::response([], 500),
        ]);

        $nlpService = app(NlpValidationService::class);
        $result = $nlpService->predict('Bansos beras PKH tidak cair dan dipotong oknum.');

        $this->assertEquals('Bantuan Sosial', $result['kategori']);
        $this->assertEquals('fallback', $result['status']);
    }

    public function test_dashboard_perlu_review_filter(): void
    {
        // Buat 1 tiket normal & 1 tiket perlu_review
        Aduan::create([
            'kode_tiket' => 'BDS-20260823-0001',
            'teks_aduan' => 'Jalan berlubang di Soreang',
            'kategori' => 'Jalan Rusak',
            'kategori_model_lokal' => 'Jalan Rusak',
            'confidence_kategori' => 0.95,
            'urgensi' => 'Sedang',
            'status' => 'baru',
            'perlu_review' => false,
        ]);

        Aduan::create([
            'kode_tiket' => 'BDS-20260823-0002',
            'teks_aduan' => 'Sampah menumpuk menutup akses jalan raya',
            'kategori' => 'Sampah/Banjir',
            'kategori_model_lokal' => 'Jalan Rusak',
            'confidence_kategori' => 0.80,
            'urgensi' => 'Tinggi',
            'status' => 'baru',
            'perlu_review' => true,
        ]);

        $response = $this->actingAs($this->admin)->get('/dashboard?perlu_review=true');
        $response->assertStatus(200);

        // Hanya tiket 0002 yang muncul di page
        $response->assertSee('BDS-20260823-0002');
    }

    public function test_validasi_klasifikasi_job_updates_aduan(): void
    {
        Http::fake([
            '*/predict' => Http::response([
                'status' => 'success',
                'kategori' => 'Keamanan/Ketertiban',
                'confidence' => 0.92,
                'model' => 'local_nlp_svm',
            ], 200),
        ]);

        $aduan = Aduan::create([
            'kode_tiket' => 'BDS-20260823-0003',
            'teks_aduan' => 'Aksi balap liar dan begal di jalan raya Katapang',
            'kategori' => 'Jalan Rusak', // Salah klasifikasi awal
            'confidence_kategori' => 0.85,
            'urgensi' => 'Tinggi',
            'status' => 'baru',
            'perlu_review' => false,
        ]);

        ValidasiKlasifikasiJob::dispatchSync($aduan);

        $fresh = $aduan->fresh();
        $this->assertEquals('Keamanan/Ketertiban', $fresh->kategori_model_lokal);
        $this->assertTrue((bool) $fresh->perlu_review);
    }
}
