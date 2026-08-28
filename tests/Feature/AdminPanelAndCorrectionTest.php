<?php

use App\Models\Aduan;
use App\Models\Dinas;
use App\Models\KategoriDinasMapping;
use App\Models\KoreksiKategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed essential Dinas & Mappings
    $this->dputr = Dinas::create([
        'nama_dinas' => 'Dinas Pekerjaan Umum dan Tata Ruang',
        'kode_dinas' => 'DPUTR',
        'email_dinas' => 'dputr@bandungkab.go.id',
        'telepon' => '022-5891234',
        'alamat' => 'Soreang, Kab. Bandung',
    ]);

    $this->dlh = Dinas::create([
        'nama_dinas' => 'Dinas Lingkungan Hidup',
        'kode_dinas' => 'DLH',
        'email_dinas' => 'dlh@bandungkab.go.id',
        'telepon' => '022-5895678',
        'alamat' => 'Soreang, Kab. Bandung',
    ]);

    $this->mappingJalan = KategoriDinasMapping::create([
        'kategori' => 'Jalan Rusak',
        'dinas_id' => $this->dputr->id,
        'deskripsi' => 'Kerusakan aspal, lubang jalan, dan jembatan.',
    ]);

    $this->mappingSampah = KategoriDinasMapping::create([
        'kategori' => 'Sampah/Banjir',
        'dinas_id' => $this->dlh->id,
        'deskripsi' => 'Tumpukan sampah liar, drainase tersumbat, dan banjir.',
    ]);

    $this->user = User::factory()->create([
        'name' => 'Staf Pengawas',
        'email' => 'staf@bandungkab.go.id',
        'dinas_id' => $this->dputr->id,
    ]);
});

test('staff can correct ticket category, auto-reroutes dinas, and clears perlu_review', function () {
    $aduan = Aduan::create([
        'kode_tiket' => 'BDS-20260828-0001',
        'teks_aduan' => 'Sampah menumpuk di saluran air jalan raya hingga meluap ke badan jalan.',
        'kategori' => 'Jalan Rusak', // AI salah klasifikasi awal
        'confidence_kategori' => 0.65,
        'urgensi' => 'Tinggi',
        'dinas_id' => $this->dputr->id,
        'status' => 'baru',
        'latitude' => -7.0252,
        'longitude' => 107.5197,
        'alamat' => 'Soreang, Kab. Bandung',
        'perlu_review' => true,
    ]);

    $response = $this->actingAs($this->user)
        ->patchJson(route('dashboard.aduan.koreksi-kategori', $aduan->id), [
            'kategori' => 'Sampah/Banjir',
            'alasan' => 'Fokus utama adalah penumpukan sampah saluran air DLH.',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    $aduan->refresh();

    // Pastikan kategori ter-update ke Sampah/Banjir
    expect($aduan->kategori)->toBe('Sampah/Banjir');
    // Pastikan dinas ter-reroute ke DLH
    expect($aduan->dinas_id)->toBe($this->dlh->id);
    // Pastikan flag perlu_review di-clear
    expect($aduan->perlu_review)->toBeFalse();

    // Pastikan tersimpan di audit trail KoreksiKategori
    $this->assertDatabaseHas('koreksi_kategoris', [
        'aduan_id' => $aduan->id,
        'user_id' => $this->user->id,
        'kategori_lama' => 'Jalan Rusak',
        'kategori_baru' => 'Sampah/Banjir',
        'dinas_lama_id' => $this->dputr->id,
        'dinas_baru_id' => $this->dlh->id,
    ]);
});

test('admin can CRUD kategori dinas mappings', function () {
    // 1. Index
    $this->actingAs($this->user)
        ->get(route('admin.kategori-mapping.index'))
        ->assertOk();

    // 2. Store
    $this->actingAs($this->user)
        ->post(route('admin.kategori-mapping.store'), [
            'kategori' => 'Penerangan Jalan Umum (PJU)',
            'dinas_id' => $this->dputr->id,
            'deskripsi' => 'Lampu PJU mati atau roboh.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('kategori_dinas_mappings', [
        'kategori' => 'Penerangan Jalan Umum (PJU)',
        'dinas_id' => $this->dputr->id,
    ]);

    $newMapping = KategoriDinasMapping::where('kategori', 'Penerangan Jalan Umum (PJU)')->first();

    // 3. Update
    $this->actingAs($this->user)
        ->put(route('admin.kategori-mapping.update', $newMapping->id), [
            'kategori' => 'Penerangan Jalan Umum & Traffic Light',
            'dinas_id' => $this->dputr->id,
            'deskripsi' => 'Lampu PJU dan rambu lalu lintas rusak.',
        ])
        ->assertRedirect();

    expect($newMapping->fresh()->kategori)->toBe('Penerangan Jalan Umum & Traffic Light');

    // 4. Destroy
    $this->actingAs($this->user)
        ->delete(route('admin.kategori-mapping.destroy', $newMapping->id))
        ->assertRedirect();

    $this->assertDatabaseMissing('kategori_dinas_mappings', [
        'id' => $newMapping->id,
    ]);
});

test('admin can view aggregated statistics page', function () {
    Aduan::create([
        'kode_tiket' => 'BDS-20260828-0002',
        'teks_aduan' => 'Jalan berlubang cukup dalam di Soreang dekat perempatan.',
        'kategori' => 'Jalan Rusak',
        'urgensi' => 'Sedang',
        'dinas_id' => $this->dputr->id,
        'status' => 'selesai',
        'latitude' => -7.0252,
        'longitude' => 107.5197,
        'alamat' => 'Soreang, Kab. Bandung',
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('admin.statistik.index'));

    $response->assertOk();
});

test('admin can export retraining aduan dataset as CSV', function () {
    Aduan::create([
        'kode_tiket' => 'BDS-20260828-0003',
        'teks_aduan' => 'Sampah menumpuk di pinggir sungai Citarum Dayeuhkolot.',
        'kategori' => 'Sampah/Banjir',
        'urgensi' => 'Tinggi',
        'dinas_id' => $this->dlh->id,
        'status' => 'diproses',
        'latitude' => -6.9839,
        'longitude' => 107.6253,
        'alamat' => 'Dayeuhkolot, Kab. Bandung',
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('admin.statistik.export-csv'));

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});
