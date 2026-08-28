<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aduan;
use App\Models\Dinas;
use App\Models\KoreksiKategori;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StatistikController extends Controller
{
    /**
     * Tampilkan halaman dasbor statistik agregat & performa AI.
     */
    public function index(Request $request): Response
    {
        $totalAduan = Aduan::count();

        // 1. Distribusi per Kategori
        $kategoriDistribution = Aduan::select('kategori', DB::raw('count(*) as total'))
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) use ($totalAduan) {
                return [
                    'kategori' => $item->kategori,
                    'total' => $item->total,
                    'percentage' => $totalAduan > 0 ? round(($item->total / $totalAduan) * 100, 1) : 0,
                ];
            });

        // 2. Distribusi per Status
        $statusCounts = [
            'baru' => Aduan::where('status', 'baru')->count(),
            'diproses' => Aduan::where('status', 'diproses')->count(),
            'selesai' => Aduan::where('status', 'selesai')->count(),
            'ditolak' => Aduan::where('status', 'ditolak')->count(),
        ];

        // 3. Distribusi per Urgensi
        $urgensiCounts = [
            'Darurat' => Aduan::where('urgensi', 'Darurat')->count(),
            'Tinggi' => Aduan::where('urgensi', 'Tinggi')->count(),
            'Sedang' => Aduan::where('urgensi', 'Sedang')->count(),
            'Rendah' => Aduan::where('urgensi', 'Rendah')->count(),
        ];

        // 4. Performa & Tingkat Penyelesaian per Dinas
        $dinasStats = Dinas::withCount([
            'aduans as total_masuk',
            'aduans as total_selesai' => function ($query) {
                $query->where('status', 'selesai');
            },
            'aduans as total_diproses' => function ($query) {
                $query->where('status', 'diproses');
            },
        ])->get()->map(function ($dinas) {
            $resolutionRate = $dinas->total_masuk > 0 
                ? round(($dinas->total_selesai / $dinas->total_masuk) * 100, 1) 
                : 0;
            return [
                'id' => $dinas->id,
                'nama_dinas' => $dinas->nama_dinas,
                'kode_dinas' => $dinas->kode_dinas,
                'total_masuk' => $dinas->total_masuk,
                'total_selesai' => $dinas->total_selesai,
                'total_diproses' => $dinas->total_diproses,
                'resolution_rate' => $resolutionRate,
            ];
        });

        // 5. Tren Bulanan / 7 Hari Terakhir
        $dailyTrends = Aduan::select(DB::raw('DATE(created_at) as tanggal'), DB::raw('count(*) as total'))
            ->where('created_at', '>=', Carbon::now()->subDays(14))
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        // 6. Matriks Performa AI (Dual-Layer AI & Active Learning Correction Rate)
        $totalKoreksi = KoreksiKategori::count();
        $totalPerluReview = Aduan::where('perlu_review', true)->count();
        $aiAgreementCount = Aduan::where('perlu_review', false)->count();
        $aiAccuracyRate = $totalAduan > 0 
            ? round((($totalAduan - $totalKoreksi) / $totalAduan) * 100, 1) 
            : 100;

        $aiPerformance = [
            'total_aduan' => $totalAduan,
            'total_koreksi' => $totalKoreksi,
            'total_perlu_review' => $totalPerluReview,
            'agreement_count' => $aiAgreementCount,
            'accuracy_rate' => max(0, $aiAccuracyRate),
        ];

        // 7. Riwayat Koreksi Terakhir (Audit Trail)
        $recentCorrections = KoreksiKategori::with(['aduan:id,kode_tiket,teks_aduan', 'user:id,name', 'dinasLama', 'dinasBaru'])
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('Admin/Statistik', [
            'totalAduan' => $totalAduan,
            'kategoriDistribution' => $kategoriDistribution,
            'statusCounts' => $statusCounts,
            'urgensiCounts' => $urgensiCounts,
            'dinasStats' => $dinasStats,
            'dailyTrends' => $dailyTrends,
            'aiPerformance' => $aiPerformance,
            'recentCorrections' => $recentCorrections,
        ]);
    }

    /**
     * Ekspor dataset aduan & riwayat koreksi ke format CSV (Bahan Retraining Model NLP).
     */
    public function exportCsv(): StreamedResponse
    {
        $fileName = 'dataset_aduan_koreksi_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM untuk Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Header
            fputcsv($handle, [
                'ID Tiket',
                'Kode Tiket',
                'Teks Aduan',
                'Kategori Final',
                'Kategori Model Lokal',
                'Tingkat Urgensi',
                'Dinas Penanggung Jawab',
                'Status',
                'Alamat / Wilayah',
                'Sumber Klasifikasi',
                'Pernah Dikoreksi',
                'Tanggal Masuk',
            ]);

            Aduan::with(['dinas', 'koreksiHistori'])
                ->chunk(200, function ($aduans) use ($handle) {
                    foreach ($aduans as $aduan) {
                        fputcsv($handle, [
                            $aduan->id,
                            $aduan->kode_tiket,
                            $aduan->teks_aduan,
                            $aduan->kategori,
                            $aduan->kategori_model_lokal ?? '-',
                            $aduan->urgensi,
                            $aduan->dinas ? $dinasName = $aduan->dinas->nama_dinas : 'Belum ditentukan',
                            $aduan->status,
                            $aduan->alamat ?? '-',
                            $aduan->sumber_klasifikasi,
                            $aduan->koreksiHistori->isNotEmpty() ? 'Ya (' . $aduan->koreksiHistori->count() . 'x)' : 'Tidak',
                            $aduan->created_at->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
