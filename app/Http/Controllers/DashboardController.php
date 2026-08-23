<?php

namespace App\Http\Controllers;

use App\Events\AduanStatusUpdated;
use App\Models\Aduan;
use App\Models\Dinas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Tampilkan Halaman Utama Dashboard CRM Staf & OPD Dinas.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // 31 Kecamatan Resmi Kabupaten Bandung
        $kecamatanList = [
            'Arjasari', 'Baleendah', 'Banjaran', 'Boojongsoang', 'Cangkuang',
            'Cicalengka', 'Cikancung', 'Cilengkrang', 'Cileunyi', 'Cimaung',
            'Cimenyan', 'Ciparay', 'Ciwidey', 'Dayeuhkolot', 'Ibun',
            'Katapang', 'Kertasari', 'Kutawaringin', 'Majalaya', 'Margaasih',
            'Margahayu', 'Nagreg', 'Pacet', 'Pameungpeuk', 'Pangalengan',
            'Paseh', 'Pasirjambu', 'Rancabali', 'Rancaekek', 'Solokanjeruk', 'Soreang'
        ];

        // Query Dasar
        $query = Aduan::with('dinas')->latest();

        // Jika user terikat ke dinas tertentu dan bukan super-admin, prioritaskan dinasnya
        if ($user && $user->dinas_id) {
            // Bisa difilter per dinas jika diinginkan
        }

        // Filter: Status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter: Urgensi
        if ($request->filled('urgensi') && $request->urgensi !== 'all') {
            $query->where('urgensi', $request->urgensi);
        }

        // Filter: Kategori
        if ($request->filled('kategori') && $request->kategori !== 'all') {
            $query->where('kategori', $request->kategori);
        }

        // Filter: Dinas
        if ($request->filled('dinas_id') && $request->dinas_id !== 'all') {
            $query->where('dinas_id', $request->dinas_id);
        }

        // Filter: Perlu Review
        if ($request->filled('perlu_review') && ($request->perlu_review === 'true' || $request->perlu_review === '1')) {
            $query->where('perlu_review', true);
        }

        // Filter: Kecamatan
        if ($request->filled('kecamatan') && $request->kecamatan !== 'all') {
            $query->where('alamat', 'LIKE', '%' . $request->kecamatan . '%');
        }

        // Search: Keyword / Kode Tiket / Nama Pelapor / Alamat
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('kode_tiket', 'LIKE', "%{$search}%")
                  ->orWhere('teks_aduan', 'LIKE', "%{$search}%")
                  ->orWhere('nama_pelapor', 'LIKE', "%{$search}%")
                  ->orWhere('alamat', 'LIKE', "%{$search}%")
                  ->orWhere('kategori', 'LIKE', "%{$search}%");
            });
        }

        // Sorting
        if ($request->sort === 'urgensi') {
            // Urutkan Darurat -> Tinggi -> Sedang -> Rendah
            $query->orderByRaw("
                CASE 
                    WHEN urgensi = 'Darurat' THEN 1
                    WHEN urgensi = 'Tinggi' THEN 2
                    WHEN urgensi = 'Sedang' THEN 3
                    WHEN urgensi = 'Rendah' THEN 4
                    ELSE 5
                END ASC
            ");
        } else {
            $query->latest();
        }

        $aduans = $query->paginate(30)->withQueryString();

        // Hitung Statistik Metrik KPI dengan Caching 60 detik (Sangat Cepat & Skalabel)
        $stats = \Illuminate\Support\Facades\Cache::remember('dashboard_stats_kpi', 60, function () {
            return [
                'total' => Aduan::count(),
                'baru' => Aduan::where('status', 'baru')->count(),
                'diproses' => Aduan::where('status', 'diproses')->count(),
                'selesai' => Aduan::where('status', 'selesai')->count(),
                'ditolak' => Aduan::where('status', 'ditolak')->count(),
                'darurat' => Aduan::whereIn('urgensi', ['Darurat', 'Tinggi'])->whereIn('status', ['baru', 'diproses'])->count(),
                'perlu_review' => Aduan::where('perlu_review', true)->count(),
            ];
        });

        $dinasList = Dinas::orderBy('nama_dinas')->get();

        return Inertia::render('Dashboard', [
            'aduans' => $aduans,
            'stats' => $stats,
            'dinasList' => $dinasList,
            'kecamatanList' => $kecamatanList,
            'filters' => $request->only(['status', 'urgensi', 'kategori', 'dinas_id', 'kecamatan', 'search', 'sort']),
            'authDinas' => $user && $user->dinas ? $user->dinas : null,
        ]);
    }

    /**
     * Update Status & Catatan Tindak Lanjut Petugas pada Aduan.
     */
    public function updateStatus(Request $request, Aduan $aduan): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:baru,diproses,selesai,ditolak'],
            'catatan_petugas' => ['nullable', 'string', 'max:1000'],
        ], [
            'status.required' => 'Status tindak lanjut wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
        ]);

        $oldStatus = $aduan->status;

        $aduan->update([
            'status' => $validated['status'],
            'catatan_petugas' => $validated['catatan_petugas'] ?? $aduan->catatan_petugas,
        ]);

        // Invalidate Cache Statistik KPI
        \Illuminate\Support\Facades\Cache::forget('dashboard_stats_kpi');

        // Broadcast event live update via WebSocket Reverb (Fail-safe)
        try {
            AduanStatusUpdated::dispatch($aduan, $oldStatus);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Broadcast Reverb gagal dikirim: ' . $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => "Status tiket #{$aduan->kode_tiket} berhasil diperbarui menjadi {$aduan->status}.",
                'data' => $aduan->fresh()->load('dinas'),
            ]);
        }

        return back()->with('success', "Status tiket #{$aduan->kode_tiket} berhasil diperbarui.");
    }
}
