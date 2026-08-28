<?php

use App\Models\Dinas;
use App\Models\KategoriDinasMapping;

test('category correctly resolves to assigned dinas from dynamic mapping', function () {
    $dputr = Dinas::create([
        'nama_dinas' => 'Dinas Pekerjaan Umum dan Tata Ruang',
        'kode_dinas' => 'DPUTR',
    ]);

    $dlh = Dinas::create([
        'nama_dinas' => 'Dinas Lingkungan Hidup',
        'kode_dinas' => 'DLH',
    ]);

    KategoriDinasMapping::create([
        'kategori' => 'Jalan Rusak',
        'dinas_id' => $dputr->id,
    ]);

    KategoriDinasMapping::create([
        'kategori' => 'Sampah/Banjir',
        'dinas_id' => $dlh->id,
    ]);

    $mappingJalan = KategoriDinasMapping::where('kategori', 'Jalan Rusak')->with('dinas')->first();
    $mappingSampah = KategoriDinasMapping::where('kategori', 'Sampah/Banjir')->with('dinas')->first();

    expect($mappingJalan->dinas->kode_dinas)->toBe('DPUTR')
        ->and($mappingSampah->dinas->kode_dinas)->toBe('DLH');
});

test('unmapped category returns null for graceful fallback routing', function () {
    $mapping = KategoriDinasMapping::where('kategori', 'Kategori Tidak Dikenal')->first();
    expect($mapping)->toBeNull();
});
