<?php

use Illuminate\Support\Facades\Http;

test('geocode controller returns local landmark match for kabupaten bandung keyword', function () {
    $response = $this->getJson('/api/geocode/search?q=soreang');

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
        ]);

    $data = $response->json('data');
    expect($data)->not->toBeEmpty()
        ->and($data[0]['display_name'])->toContain('Soreang');
});

test('geocode controller parses reverse geocoding inside kabupaten bandung correctly', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            'display_name' => 'Jalan Raya Soreang, Soreang, Kabupaten Bandung, Jawa Barat, Indonesia',
            'address' => [
                'road' => 'Jalan Raya Soreang',
                'village' => 'Soreang',
                'county' => 'Kabupaten Bandung',
                'state' => 'Jawa Barat',
                'country' => 'Indonesia',
            ]
        ], 200),
    ]);

    $response = $this->getJson('/api/geocode?lat=-7.0252&lng=107.5197');

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'is_kabupaten_bandung' => true,
            ]
        ]);
});

test('geocode controller flags coordinates outside kabupaten bandung', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            'display_name' => 'Jl. Asia Afrika, Braga, Kota Bandung, Jawa Barat, Indonesia',
            'address' => [
                'road' => 'Jl. Asia Afrika',
                'suburb' => 'Braga',
                'city' => 'Kota Bandung',
                'state' => 'Jawa Barat',
                'country' => 'Indonesia',
            ]
        ], 200),
    ]);

    $response = $this->getJson('/api/geocode?lat=-6.9215&lng=107.6100');

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
            'data' => [
                'is_kabupaten_bandung' => false,
            ]
        ]);
});
