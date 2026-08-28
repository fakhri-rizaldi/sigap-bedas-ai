<?php

namespace App\Http\Controllers;

use App\Models\Aduan;
use App\Models\KategoriDinasMapping;
use App\Services\GeminiClassificationService;
use App\Services\NlpValidationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AduanPublicController extends Controller
{
    protected GeminiClassificationService $classificationService;
    protected NlpValidationService $nlpService;

    public function __construct(
        GeminiClassificationService $classificationService,
        NlpValidationService $nlpService
    ) {
        $this->classificationService = $classificationService;
        $this->nlpService = $nlpService;
    }

    /**
     * Halaman form pelaporan publik.
     * GET /lapor
     */
    public function create(): Response
    {
        $categories = KategoriDinasMapping::with('dinas:id,nama_dinas,kode_dinas')->get();

        return Inertia::render('Lapor/Create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Simpan aduan baru dari warga.
     * POST /lapor
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'teks_aduan' => ['required', 'string', 'min:10', 'max:2000'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'confidence_kategori' => ['nullable', 'numeric', 'between:0,1'],
            'urgensi' => ['nullable', 'in:Rendah,Sedang,Tinggi,Darurat'],
            'alasan_urgensi' => ['nullable', 'string', 'max:500'],
            'latitude' => ['required', 'numeric', 'between:-7.3500,-6.7800'],
            'longitude' => ['required', 'numeric', 'between:107.2500,107.9500'],
            'alamat' => ['required', 'string', 'min:5', 'max:500'],
            'foto' => ['nullable', function ($attribute, $value, $fail) {
                if ($value instanceof \Illuminate\Http\UploadedFile) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $fail('Berkas foto harus berformat JPG, JPEG, PNG, atau WEBP.');
                    }
                    if ($value->getSize() > 10 * 1024 * 1024) {
                        $fail('Ukuran berkas foto maksimal 10MB.');
                    }
                }
            }],
            'nama_pelapor' => ['nullable', 'string', 'max:100'],
            'kontak_pelapor' => ['nullable', 'string', 'max:50'],
            'email_pelapor' => ['nullable', 'email', 'max:100'],
        ], [
            'teks_aduan.required' => 'Mohon jelaskan detail keluhan atau aduan Anda.',
            'teks_aduan.min' => 'Teks aduan minimal 10 karakter agar dapat dianalisis.',
            'latitude.required' => 'Titik lokasi aduan wajib ditentukan di peta.',
            'latitude.between' => 'Titik lokasi kejadian berada di luar wilayah Kabupaten Bandung. Layanan SIGAP khusus melayani wilayah Kab. Bandung.',
            'longitude.required' => 'Titik lokasi aduan wajib ditentukan di peta.',
            'longitude.between' => 'Titik lokasi kejadian berada di luar wilayah Kabupaten Bandung. Layanan SIGAP khusus melayani wilayah Kab. Bandung.',
            'alamat.required' => 'Alamat atau rincian lokasi aduan wajib diisi.',
            'email_pelapor.email' => 'Format alamat email tidak valid.',
        ]);

        $lat = (float) $validated['latitude'];
        $lng = (float) $validated['longitude'];
        $alamat = $validated['alamat'];

        // Cek apakah lokasi berada di Kota Bandung / Kota Cimahi
        $isKotaBandung = ($lat >= -6.9700 && $lat <= -6.8600 && $lng >= 107.5600 && $lng <= 107.7100);
        $isKotaCimahi = ($lat >= -6.9150 && $lat <= -6.8600 && $lng >= 107.5100 && $lng <= 107.5600);

        // Jika alamat tidak mengandung nama kecamatan resmi Kab. Bandung di area perbatasan
        $kabupatenKeywords = ['kabupaten bandung', 'soreang', 'baleendah', 'dayeuhkolot', 'bojongsoang', 'margahayu', 'margaasih', 'katapang', 'cimenyan', 'cilengkrang', 'cileunyi', 'rancaekek', 'majalaya', 'ciparay', 'banjaran', 'ciwidey', 'pangalengan'];
        $hasKabKeyword = false;
        foreach ($kabupatenKeywords as $kw) {
            if (str_contains(mb_strtolower($alamat), $kw)) {
                $hasKabKeyword = true;
                break;
            }
        }

        if (($isKotaBandung || $isKotaCimahi) && !$hasKabKeyword && (str_contains(mb_strtolower($alamat), 'kota bandung') || str_contains(mb_strtolower($alamat), 'kota cimahi'))) {
            return back()->withErrors([
                'latitude' => 'Titik lokasi berada di wilayah Kota Bandung / Cimahi. Layanan SIGAP hanya melayani aduan dalam wilayah administratif Kabupaten Bandung.',
            ])->withInput();
        }

        // Jika kategori / urgensi belum diisi dari frontend, jalankan klasifikasi otomatis di backend
        if (empty($validated['kategori']) || empty($validated['urgensi'])) {
            $aiResult = $this->classificationService->classify($validated['teks_aduan']);
            $kategori = $aiResult['kategori'];
            $confidence = $aiResult['confidence'];
            $urgensi = $aiResult['urgensi'];
            $alasan = $aiResult['alasan'];
            $sumber = $aiResult['sumber'];
        } else {
            $kategori = $validated['kategori'];
            $confidence = $validated['confidence_kategori'] ?? 0.90;
            $urgensi = $validated['urgensi'];
            $alasan = $validated['alasan_urgensi'] ?? null;
            $sumber = 'gemini_api';
        }

        // Klasifikasi Dua Lapis: Ambil prediksi dari Model NLP Mandiri (FastAPI)
        $localResult = $this->nlpService->predict($validated['teks_aduan']);
        $kategoriLokal = $localResult['kategori'] ?? null;
        $confidenceLokal = $localResult['confidence'] ?? null;

        // Evaluasi Discrepancy (Ketidaksesuaian Antar Model)
        $isCategoryMatch = ($kategoriLokal !== null && strcasecmp(trim($kategori), trim($kategoriLokal)) === 0);
        $isConfidenceLow = ($confidence < 0.70);
        $perluReview = (!$isCategoryMatch || $isConfidenceLow);

        // Auto routing ke dinas terkait
        $mapping = KategoriDinasMapping::where('kategori', $kategori)->first();
        $dinasId = $mapping ? $mapping->dinas_id : null;

        // Upload foto jika ada (Mendukung multipart UploadedFile maupun Base64 Data URL)
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $uploadedPath = $request->file('foto')->store('aduans', 'public');
            $fotoPath = '/storage/' . $uploadedPath;
        } elseif (is_string($request->foto) && str_starts_with($request->foto, 'data:image/')) {
            if (preg_match('/^data:image\/(\w+);base64,/', $request->foto, $type)) {
                $data = substr($request->foto, strpos($request->foto, ',') + 1);
                $ext = strtolower($type[1]);
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $ext = 'jpg';
                }
                $decodedData = base64_decode($data);
                if ($decodedData !== false) {
                    $fileName = 'aduans/' . \Illuminate\Support\Str::random(40) . '.' . $ext;
                    \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $decodedData);
                    $fotoPath = '/storage/' . $fileName;
                }
            }
        }

        $aduan = Aduan::create([
            'teks_aduan' => $validated['teks_aduan'],
            'kategori' => $kategori,
            'confidence_kategori' => $confidence,
            'urgensi' => $urgensi,
            'alasan_urgensi' => $alasan,
            'dinas_id' => $dinasId,
            'status' => 'baru',
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'foto_path' => $fotoPath,
            'nama_pelapor' => $validated['nama_pelapor'] ?? null,
            'kontak_pelapor' => $validated['kontak_pelapor'] ?? null,
            'email_pelapor' => $validated['email_pelapor'] ?? null,
            'sumber_klasifikasi' => $sumber,
            'perlu_review' => $perluReview,
            'kategori_model_lokal' => $kategoriLokal,
            'confidence_model_lokal' => $confidenceLokal,
        ]);

        // Broadcast event live update ke dashboard staf & dinas (Fail-safe jika Reverb server belum berjalan)
        try {
            \App\Events\AduanCreated::dispatch($aduan);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Broadcast Reverb gagal dikirim (Reverb server offline): ' . $e->getMessage());
        }

        // Invalidate Cache Statistik KPI
        \Illuminate\Support\Facades\Cache::forget('dashboard_stats_kpi');

        return redirect()->route('lapor.success', ['kodeTiket' => $aduan->kode_tiket])
            ->with('success', 'Laporan Anda berhasil dikirim.');
    }

    /**
     * Halaman konfirmasi sukses setelah kirim laporan.
     * GET /lapor/sukses/{kodeTiket}
     */
    public function success(string $kodeTiket): Response
    {
        $aduan = Aduan::with('dinas')->where('kode_tiket', $kodeTiket)->firstOrFail();

        return Inertia::render('Lapor/Success', [
            'aduan' => $aduan,
        ]);
    }

    /**
     * Halaman Publik Pelacakan Status Laporan Aduan Warga.
     * GET /lapor/status
     * GET /lapor/status/{kodeTiket}
     */
    public function track(Request $request, ?string $kodeTiket = null): Response
    {
        $searchQuery = trim($request->input('kode', $kodeTiket ?? ''));
        $aduan = null;
        $searched = false;

        if (!empty($searchQuery)) {
            $searched = true;
            $searchQuery = strtoupper($searchQuery);
            $aduan = Aduan::with('dinas')
                ->where('kode_tiket', $searchQuery)
                ->first();
        }

        return Inertia::render('Lapor/Status', [
            'aduan' => $aduan,
            'searchKode' => $searchQuery,
            'searched' => $searched,
        ]);
    }
}
