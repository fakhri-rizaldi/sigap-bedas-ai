<?php

use App\Models\Aduan;
use App\Models\Dinas;
use App\Models\KategoriDinasMapping;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->dputr = Dinas::create([
        'nama_dinas' => 'Dinas Pekerjaan Umum dan Tata Ruang',
        'kode_dinas' => 'DPUTR',
    ]);

    KategoriDinasMapping::create([
        'kategori' => 'Jalan Rusak',
        'dinas_id' => $this->dputr->id,
    ]);
});

test('submitting repeated identical complaint hits cache and does not call external LLM repeatedly', function () {
    $callCount = 0;

    Http::fake([
        'generativelanguage.googleapis.com/*' => function () use (&$callCount) {
            $callCount++;
            return Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'kategori' => 'Jalan Rusak',
                                        'confidence' => 0.95,
                                        'urgensi' => 'Tinggi',
                                        'alasan' => 'Kerusakan aspal membahayakan pengendara.',
                                    ])
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200);
        },
        '127.0.0.1:8001/*' => Http::response([
            'kategori' => 'Jalan Rusak',
            'confidence' => 0.94,
        ], 200),
    ]);

    Cache::flush();

    // Call 1: Live API
    $response1 = $this->postJson('/api/aduan/classify', [
        'teks_aduan' => 'Jalan raya Kopo Sayati berlubang parah sering mencelakakan pengendara motor.',
    ]);
    $response1->assertOk();
    expect($callCount)->toBe(1);

    // Call 2: Identical prompt should hit Cache
    $response2 = $this->postJson('/api/aduan/classify', [
        'teks_aduan' => 'Jalan raya Kopo Sayati berlubang parah sering mencelakakan pengendara motor.',
    ]);
    $response2->assertOk();
    expect($callCount)->toBe(1); // Call count stays 1 (cached!)
});

test('system gracefully processes complaint submission when both Gemini and FastAPI are offline', function () {
    // Simulasi kegagalan network total (Exception / 500)
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response(null, 500),
        '127.0.0.1:8001/*' => Http::response(null, 500),
    ]);

    $payload = [
        'teks_aduan' => 'Jalan rusak berlubang parah di Soreang membahayakan warga.',
        'latitude' => -7.0252,
        'longitude' => 107.5197,
        'alamat' => 'Soreang, Kab. Bandung',
    ];

    $response = $this->post(route('lapor.store'), $payload);

    // Submission harus tetap berhasil via Rule-Based fallback tanpa 500 error
    $response->assertRedirect();
    $this->assertDatabaseHas('aduans', [
        'alamat' => 'Soreang, Kab. Bandung',
        'kategori' => 'Jalan Rusak',
    ]);
});
