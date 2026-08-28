<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dinas;
use App\Models\KategoriDinasMapping;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KategoriMappingController extends Controller
{
    /**
     * Tampilkan daftar mapping kategori ke instansi dinas.
     */
    public function index(): Response
    {
        $mappings = KategoriDinasMapping::with('dinas')->orderBy('kategori')->get();
        $dinasList = Dinas::orderBy('nama_dinas')->get();

        return Inertia::render('Admin/KategoriMapping', [
            'mappings' => $mappings,
            'dinasList' => $dinasList,
        ]);
    }

    /**
     * Simpan mapping kategori baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kategori' => ['required', 'string', 'max:100', 'unique:kategori_dinas_mappings,kategori'],
            'dinas_id' => ['required', 'exists:dinas,id'],
            'deskripsi' => ['nullable', 'string', 'max:500'],
        ], [
            'kategori.required' => 'Nama kategori wajib diisi.',
            'kategori.unique' => 'Kategori ini sudah terdaftar dalam sistem.',
            'dinas_id.required' => 'Pilih dinas instansi penanggung jawab.',
            'dinas_id.exists' => 'Dinas yang dipilih tidak valid.',
        ]);

        KategoriDinasMapping::create($validated);

        return back()->with('success', "Mapping kategori '{$validated['kategori']}' berhasil ditambahkan.");
    }

    /**
     * Update mapping kategori yang ada.
     */
    public function update(Request $request, KategoriDinasMapping $mapping): RedirectResponse
    {
        $validated = $request->validate([
            'kategori' => ['required', 'string', 'max:100', 'unique:kategori_dinas_mappings,kategori,' . $mapping->id],
            'dinas_id' => ['required', 'exists:dinas,id'],
            'deskripsi' => ['nullable', 'string', 'max:500'],
        ], [
            'kategori.required' => 'Nama kategori wajib diisi.',
            'kategori.unique' => 'Kategori ini sudah digunakan oleh mapping lain.',
            'dinas_id.required' => 'Pilih dinas instansi penanggung jawab.',
        ]);

        $mapping->update($validated);

        return back()->with('success', "Mapping kategori '{$mapping->kategori}' berhasil diperbarui.");
    }

    /**
     * Hapus mapping kategori.
     */
    public function destroy(KategoriDinasMapping $mapping): RedirectResponse
    {
        $namaKategori = $mapping->kategori;
        $mapping->delete();

        return back()->with('success', "Mapping kategori '{$namaKategori}' berhasil dihapus.");
    }
}
