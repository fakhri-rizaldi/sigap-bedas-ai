<?php

namespace Tests\Feature;

use App\Services\GeminiClassificationService;
use App\Services\RuleBasedClassificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiClassificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_rule_based_fallback_classifies_jalan_rusak(): void
    {
        $service = app(RuleBasedClassificationService::class);
        $result = $service->classify('Jalan raya Soreang berlubang parah sering terjadi kecelakaan');

        $this->assertEquals('Jalan Rusak', $result['kategori']);
        $this->assertEquals('Tinggi', $result['urgensi']);
        $this->assertEquals('rule_based_fallback', $result['sumber']);
        $this->assertGreaterThanOrEqual(0.5, $result['confidence']);
    }

    public function test_rule_based_fallback_classifies_lingkungan(): void
    {
        $service = app(RuleBasedClassificationService::class);
        $result = $service->classify('Tumpukan sampah liar di pinggir sungai bau busuk menyengat');

        $this->assertEquals('Lingkungan & Drainase', $result['kategori']);
        $this->assertEquals('rule_based_fallback', $result['sumber']);
    }

    public function test_gemini_service_uses_api_when_key_is_set_and_successful(): void
    {
        Config::set('services.gemini.api_key', 'test-fake-key');

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'kategori' => 'Jalan Rusak',
                                        'confidence' => 0.96,
                                        'urgensi' => 'Tinggi',
                                        'alasan' => 'Lubang di jalan raya membahayakan pengguna kendaraan.',
                                    ])
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $service = app(GeminiClassificationService::class);
        $result = $service->classify('Aspal jalan Soreang amblas');

        $this->assertEquals('Jalan Rusak', $result['kategori']);
        $this->assertEquals(0.96, $result['confidence']);
        $this->assertEquals('Tinggi', $result['urgensi']);
        $this->assertEquals('gemini_api', $result['sumber']);
    }

    public function test_gemini_service_falls_back_when_api_fails(): void
    {
        Config::set('services.gemini.api_key', 'test-fake-key');

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response('Internal Server Error', 500),
        ]);

        $service = app(GeminiClassificationService::class);
        $result = $service->classify('Bansos PKH tidak cair dan salah sasaran');

        $this->assertEquals('Bantuan Sosial', $result['kategori']);
        $this->assertEquals('rule_based_fallback', $result['sumber']);
    }

    public function test_gemini_service_caches_results(): void
    {
        Config::set('services.gemini.api_key', 'test-fake-key');

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'kategori' => 'Keamanan & Ketertiban',
                                        'confidence' => 0.92,
                                        'urgensi' => 'Tinggi',
                                        'alasan' => 'Aktivitas balap liar meresahkan.',
                                    ])
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $service = app(GeminiClassificationService::class);
        $text = 'Ada balap liar di jalan baru Soreang';
        
        $result1 = $service->classify($text);
        $result2 = $service->classify($text);

        Http::assertSentCount(1);
        $this->assertEquals($result1, $result2);
    }

    public function test_api_aduan_classify_endpoint_success(): void
    {
        Config::set('services.gemini.api_key', 'test-fake-key');

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'kategori' => 'Lingkungan & Drainase',
                                        'confidence' => 0.94,
                                        'urgensi' => 'Sedang',
                                        'alasan' => 'Saluran drainase tersumbat.',
                                    ])
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $response = $this->postJson('/api/aduan/classify', [
            'teks_aduan' => 'Saluran drainase di depan rumah tersumbat sampah',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'kategori' => 'Lingkungan & Drainase',
                    'confidence' => 0.94,
                    'urgensi' => 'Sedang',
                    'sumber' => 'gemini_api',
                ],
            ]);
    }

    public function test_api_aduan_classify_endpoint_validates_input(): void
    {
        $response = $this->postJson('/api/aduan/classify', [
            'teks_aduan' => 'abc', // Kurang dari 5 karakter
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['teks_aduan']);
    }
}
