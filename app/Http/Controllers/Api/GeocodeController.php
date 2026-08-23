<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodeController extends Controller
{
    /**
     * Master Data 31 Kecamatan & Landmark Utama Kabupaten Bandung
     * Digunakan untuk pencarian instan dan akurasi tinggi (100% presisi lokal).
     */
    protected array $localLandmarks = [
        ['name' => 'Kecamatan Soreang', 'keyword' => 'soreang', 'lat' => -7.0252, 'lng' => 107.5197, 'type' => 'Kecamatan / Ibukota Kab. Bandung'],
        ['name' => 'Komplek Pemkab Bandung (Soreang)', 'keyword' => 'pemkab bandung kantor bupati soreang', 'lat' => -7.0285, 'lng' => 107.5255, 'type' => 'Pusat Pemerintahan'],
        ['name' => 'Stadion Si Jalak Harupat (Kutawaringin)', 'keyword' => 'jalak harupat stadion kutawaringin', 'lat' => -7.0016, 'lng' => 107.5298, 'type' => 'Fasilitas Publik'],
        ['name' => 'Kecamatan Baleendah', 'keyword' => 'baleendah bale endah', 'lat' => -6.9950, 'lng' => 107.6335, 'type' => 'Kecamatan'],
        ['name' => 'Kecamatan Dayeuhkolot', 'keyword' => 'dayeuhkolot dayeuh kolot jembatan citarum', 'lat' => -6.9839, 'lng' => 107.6253, 'type' => 'Kecamatan'],
        ['name' => 'Kecamatan Banjaran', 'keyword' => 'banjaran pasar banjaran', 'lat' => -7.0450, 'lng' => 107.5878, 'type' => 'Kecamatan'],
        ['name' => 'Kecamatan Majalaya', 'keyword' => 'majalaya pasar majalaya alun alun majalaya', 'lat' => -7.0514, 'lng' => 107.7567, 'type' => 'Kecamatan'],
        ['name' => 'Kecamatan Ciparay', 'keyword' => 'ciparay pasar ciparay', 'lat' => -7.0392, 'lng' => 107.7125, 'type' => 'Kecamatan'],
        ['name' => 'Kecamatan Bojongsoang', 'keyword' => 'bojongsoang bojong soang telkom university', 'lat' => -6.9742, 'lng' => 107.6401, 'type' => 'Kecamatan'],
        ['name' => 'Kecamatan Margahayu', 'keyword' => 'margahayu sayati lanud sulaiman kopo', 'lat' => -6.9789, 'lng' => 107.5755, 'type' => 'Kecamatan'],
        ['name' => 'Kecamatan Margaasih', 'keyword' => 'margaasih marga asih nanjung cigondewah', 'lat' => -6.9536, 'lng' => 107.5458, 'type' => 'Kecamatan'],
        ['name' => 'Kecamatan Katapang', 'keyword' => 'katapang cilampeni', 'lat' => -7.0055, 'lng' => 107.5685, 'type' => 'Kecamatan'],
        ['name' => 'Kecamatan Cileunyi', 'keyword' => 'cileunyi tol cileunyi panyawangan', 'lat' => -6.9388, 'lng' => 107.7478, 'type' => 'Kecamatan'],
        ['name' => 'Kecamatan Rancaekek', 'keyword' => 'rancaekek stasiun rancaekek', 'lat' => -6.9678, 'lng' => 107.7656, 'type' => 'Kecamatan'],
        ['name' => 'Kecamatan Cicalengka', 'keyword' => 'cicalengka stasiun cicalengka curug cinulang', 'lat' => -6.9845, 'lng' => 107.8345, 'type' => 'Kecamatan'],
        ['name' => 'Kecamatan Nagreg', 'keyword' => 'nagreg lingkar nagreg cagak', 'lat' => -7.0255, 'lng' => 107.8920, 'type' => 'Kecamatan'],
        ['name' => 'Kecamatan Ciwidey', 'keyword' => 'ciwidey kawah putih alun alun ciwidey', 'lat' => -7.0945, 'lng' => 107.4589, 'type' => 'Kecamatan / Wisata'],
        ['name' => 'Kecamatan Rancabali', 'keyword' => 'rancabali situ patenggang ranca upas', 'lat' => -7.1450, 'lng' => 107.3950, 'type' => 'Kecamatan / Wisata'],
        ['name' => 'Kecamatan Pasirjambu', 'keyword' => 'pasirjambu pasir jambu barusen', 'lat' => -7.0789, 'lng' => 107.4855, 'type' => 'Kecamatan'],
        ['name' => 'Kecamatan Pangalengan', 'keyword' => 'pangalengan situ cileunca perkebunan teh', 'lat' => -7.1722, 'lng' => 107.5656, 'type' => 'Kecamatan / Wisata'],
        ['name' => 'Kecamatan Cimaung', 'keyword' => 'cimaung gunung puntang', 'lat' => -7.0725, 'lng' => 107.5520, 'type' => 'Kecamatan'],
        ['name' => 'Kecamatan Arjasari', 'keyword' => 'arjasari wargaluyu', 'lat' => -7.0589, 'lng' => 107.6189, 'type' => 'Kecamatan'],
        ['name' => 'Kecamatan Cimenyan', 'keyword' => 'cimenyan padasuka tebing keraton', 'lat' => -6.8689, 'lng' => 107.6655, 'type' => 'Kecamatan'],
        ['name' => 'Kecamatan Cilengkrang', 'keyword' => 'cilengkrang manglayang', 'lat' => -6.8922, 'lng' => 107.7125, 'type' => 'Kecamatan'],
        ['name' => 'Kecamatan Kertasari', 'keyword' => 'kertasari situ cisanti hulu citarum', 'lat' => -7.2155, 'lng' => 107.6855, 'type' => 'Kecamatan'],
        ['name' => 'Kecamatan Pacet', 'keyword' => 'pacet maruyung', 'lat' => -7.1055, 'lng' => 107.7255, 'type' => 'Kecamatan'],
        ['name' => 'Kecamatan Ibun', 'keyword' => 'ibun kamojang jembatan kuning', 'lat' => -7.1255, 'lng' => 107.7855, 'type' => 'Kecamatan'],
        ['name' => 'Kecamatan Paseh', 'keyword' => 'paseh cigentur', 'lat' => -7.0755, 'lng' => 107.7885, 'type' => 'Kecamatan'],
        ['name' => 'Kecamatan Solokanjeruk', 'keyword' => 'solokanjeruk solokan jeruk', 'lat' => -7.0255, 'lng' => 107.7455, 'type' => 'Kecamatan'],
        ['name' => 'Kecamatan Cangkuang', 'keyword' => 'cangkuang nagrak bandasari', 'lat' => -7.0385, 'lng' => 107.5525, 'type' => 'Kecamatan'],
        ['name' => 'Kecamatan Kutawaringin', 'keyword' => 'kutawaringin jelegong kopo', 'lat' => -7.0085, 'lng' => 107.5125, 'type' => 'Kecamatan'],
        ['name' => 'Jl. Raya Kopo - Sayati (Margahayu)', 'keyword' => 'kopo sayati jalan raya kopo margahayu', 'lat' => -6.9745, 'lng' => 107.5812, 'type' => 'Jalan Utama'],
        ['name' => 'Jl. Raya Soreang - Banjaran', 'keyword' => 'jalan raya soreang banjaran', 'lat' => -7.0350, 'lng' => 107.5550, 'type' => 'Jalan Utama'],
        ['name' => 'Jl. Raya Bojongsoang - Dayeuhkolot', 'keyword' => 'jalan raya bojongsoang dayeuhkolot', 'lat' => -6.9810, 'lng' => 107.6320, 'type' => 'Jalan Utama'],
        ['name' => 'RSUD Bedas Otista Soreang', 'keyword' => 'rsud otista soreang rumah sakit soreang', 'lat' => -7.0225, 'lng' => 107.5210, 'type' => 'Fasilitas Kesehatan'],
        ['name' => 'RSUD Al Ihsan (Baleendah)', 'keyword' => 'rsud al ihsan al-ihsan baleendah', 'lat' => -6.9912, 'lng' => 107.6385, 'type' => 'Fasilitas Kesehatan'],
    ];

    /**
     * Reverse Geocoding Proxy ke OpenStreetMap Nominatim.
     * GET /api/geocode?lat=&lng=
     */
    public function reverse(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $lat = round((float) $validated['lat'], 5);
        $lng = round((float) $validated['lng'], 5);

        $cacheKey = "geocode_{$lat}_{$lng}";

        $result = Cache::remember($cacheKey, 2592000, function () use ($lat, $lng) {
            try {
                $response = Http::timeout(6)
                    ->withHeaders([
                        'User-Agent' => 'SIGAP-KabBandung/1.0 (layanan.aduan@bandungkab.go.id)',
                        'Accept-Language' => 'id-ID,id;q=0.9,en;q=0.8',
                    ])
                    ->get('https://nominatim.openstreetmap.org/reverse', [
                        'format' => 'jsonv2',
                        'lat' => $lat,
                        'lon' => $lng,
                        'zoom' => 18,
                        'addressdetails' => 1,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $address = $data['address'] ?? [];
                    $displayName = $data['display_name'] ?? '';

                    $rawCity = $address['city'] ?? $address['regency'] ?? $address['county'] ?? '';
                    
                    // Deteksi ketat: Kota Bandung / Kota Cimahi / Bandung Barat / Luar Kab Bandung
                    $isOutside = str_contains($displayName, 'Kota Bandung') 
                        || str_contains($rawCity, 'Kota Bandung')
                        || str_contains($displayName, 'Kota Cimahi')
                        || str_contains($rawCity, 'Kota Cimahi')
                        || str_contains($displayName, 'Bandung Barat')
                        || str_contains($rawCity, 'Bandung Barat')
                        || (!str_contains($displayName, 'Kabupaten Bandung') && !str_contains($displayName, 'Bandung Regency'));

                    $isKabBandung = !$isOutside;

                    // Format alamat yang bersih & ringkas khas Indonesia
                    $parts = [];
                    if (!empty($address['road'])) $parts[] = $address['road'];
                    if (!empty($address['neighbourhood'])) $parts[] = $address['neighbourhood'];
                    if (!empty($address['village'])) $parts[] = 'Desa ' . $address['village'];
                    elseif (!empty($address['suburb'])) $parts[] = 'Kel. ' . $address['suburb'];
                    if (!empty($address['municipality'])) $parts[] = 'Kec. ' . $address['municipality'];
                    elseif (!empty($address['county'])) $parts[] = 'Kec. ' . $address['county'];
                    
                    $kab = $isKabBandung ? 'Kabupaten Bandung' : ($rawCity ?: 'Luar Wilayah Kab. Bandung');
                    $parts[] = $kab;

                    $cleanAddress = count($parts) > 1 ? implode(', ', $parts) : ($displayName ?: "Koordinat: {$lat}, {$lng}");

                    return [
                        'alamat_lengkap' => $cleanAddress,
                        'jalan' => $address['road'] ?? $address['neighbourhood'] ?? null,
                        'desa_kelurahan' => $address['village'] ?? $address['suburb'] ?? null,
                        'kecamatan' => $address['municipality'] ?? $address['county'] ?? null,
                        'kabupaten_kota' => $kab,
                        'provinsi' => $address['state'] ?? 'Jawa Barat',
                        'is_kabupaten_bandung' => $isKabBandung,
                    ];
                }
            } catch (Exception $e) {
                Log::warning("Gagal reverse geocode koordinat [{$lat}, {$lng}]: " . $e->getMessage());
            }

            return [
                'alamat_lengkap' => "Koordinat: {$lat}, {$lng}",
                'jalan' => null,
                'desa_kelurahan' => null,
                'kecamatan' => null,
                'kabupaten_kota' => 'Kabupaten Bandung',
                'provinsi' => 'Jawa Barat',
                'is_kabupaten_bandung' => true,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }

    /**
     * Forward Geocoding / Autocomplete Search Alamat dengan Akurasi Tinggi.
     * GET /api/geocode/search?q=
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:150'],
        ]);

        $query = trim($validated['q']);
        $queryLower = mb_strtolower($query);

        // 1. CARI DARI DATABASE LOKAL KABUPATEN BANDUNG (Hasil Instan & Akurat)
        $localResults = [];
        foreach ($this->localLandmarks as $landmark) {
            if (str_contains(mb_strtolower($landmark['name']), $queryLower) || 
                str_contains(mb_strtolower($landmark['keyword']), $queryLower)) {
                $localResults[] = [
                    'display_name' => "{$landmark['name']}, Kabupaten Bandung",
                    'lat' => $landmark['lat'],
                    'lng' => $landmark['lng'],
                    'jalan' => $landmark['name'],
                    'desa_kelurahan' => $landmark['type'],
                    'kecamatan' => 'Kabupaten Bandung',
                    'is_local' => true,
                ];
            }
        }

        // 2. QUERY KE OPENSTREETMAP NOMINATIM UNTUK PENCARIAN JALAN/GANG SPESIFIK
        $cacheKey = 'geocode_search_v2_' . md5($queryLower);
        $remoteResults = Cache::remember($cacheKey, 604800, function () use ($query) {
            try {
                // Pola pencarian dengan batasan area Kabupaten Bandung
                $searchQuery = $query . ' Kabupaten Bandung';

                $response = Http::timeout(5)
                    ->withHeaders([
                        'User-Agent' => 'SIGAP-KabBandung/1.0 (layanan.aduan@bandungkab.go.id)',
                        'Accept-Language' => 'id-ID,id;q=0.9,en;q=0.8',
                    ])
                    ->get('https://nominatim.openstreetmap.org/search', [
                        'q' => $searchQuery,
                        'format' => 'jsonv2',
                        'addressdetails' => 1,
                        'limit' => 5,
                        'countrycodes' => 'id',
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $items = [];

                    foreach ($data as $item) {
                        $addr = $item['address'] ?? [];
                        $cleanName = $item['display_name'] ?? '';
                        
                        // Ringkas display name
                        $parts = [];
                        if (!empty($addr['road'])) $parts[] = $addr['road'];
                        if (!empty($addr['village'])) $parts[] = $addr['village'];
                        elseif (!empty($addr['suburb'])) $parts[] = $addr['suburb'];
                        if (!empty($addr['municipality'])) $parts[] = $addr['municipality'];
                        elseif (!empty($addr['county'])) $parts[] = $addr['county'];
                        if (!empty($addr['city']) || !empty($addr['regency'])) {
                            $parts[] = $addr['city'] ?? $addr['regency'];
                        }

                        $formattedName = count($parts) > 1 ? implode(', ', $parts) : $cleanName;

                        $items[] = [
                            'display_name' => $formattedName,
                            'lat' => (float) ($item['lat'] ?? 0),
                            'lng' => (float) ($item['lon'] ?? 0),
                            'jalan' => $addr['road'] ?? null,
                            'desa_kelurahan' => $addr['village'] ?? $addr['suburb'] ?? null,
                            'kecamatan' => $addr['municipality'] ?? $addr['county'] ?? null,
                            'is_local' => false,
                        ];
                    }

                    return $items;
                }
            } catch (Exception $e) {
                Log::warning("Gagal remote geocode query [{$query}]: " . $e->getMessage());
            }

            return [];
        });

        // Gabungkan hasil lokal (diprioritaskan) dengan hasil OSM, buang duplikat
        $merged = array_merge($localResults, $remoteResults);
        $unique = [];
        $finalResults = [];

        foreach ($merged as $res) {
            $key = round($res['lat'], 3) . '_' . round($res['lng'], 3);
            if (!isset($unique[$key])) {
                $unique[$key] = true;
                $finalResults[] = $res;
            }
            if (count($finalResults) >= 6) break;
        }

        return response()->json([
            'status' => 'success',
            'data' => $finalResults,
        ]);
    }
}
