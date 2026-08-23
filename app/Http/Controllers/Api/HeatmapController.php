<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aduan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HeatmapController extends Controller
{
    /**
     * Mengambil data titik koordinat dan bobot urgensi aduan untuk Leaflet Heatmap.
     * GET /api/aduan/heatmap-data
     */
    public function getData(Request $request): JsonResponse
    {
        $query = Aduan::with('dinas')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', 0)
            ->where('longitude', '!=', 0);

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

        // Filter: Kecamatan
        if ($request->filled('kecamatan') && $request->kecamatan !== 'all') {
            $query->where('alamat', 'LIKE', '%' . $request->kecamatan . '%');
        }

        $aduans = $query->latest()->limit(500)->get();

        $points = $aduans->map(function ($aduan) {
            // Kalkulasi bobot (weight) berdasarkan tingkat urgensi
            $weight = match ($aduan->urgensi) {
                'Darurat' => 1.0,
                'Tinggi' => 0.8,
                'Sedang' => 0.5,
                default => 0.2, // Rendah
            };

            return [
                'id' => $aduan->id,
                'kode_tiket' => $aduan->kode_tiket,
                'lat' => (float) $aduan->latitude,
                'lng' => (float) $aduan->longitude,
                'weight' => $weight,
                'kategori' => $aduan->kategori,
                'urgensi' => $aduan->urgensi,
                'status' => $aduan->status,
                'alamat' => $aduan->alamat,
                'teks_aduan' => $aduan->teks_aduan,
                'foto_path' => $aduan->foto_path,
                'nama_pelapor' => $aduan->nama_pelapor,
                'dinas_nama' => $aduan->dinas?->nama_dinas,
                'dinas_kode' => $aduan->dinas?->kode_dinas,
                'created_at' => $aduan->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'count' => $points->count(),
            'data' => $points,
        ]);
    }
}
