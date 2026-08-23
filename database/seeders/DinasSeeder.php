<?php

namespace Database\Seeders;

use App\Models\Dinas;
use App\Models\KategoriDinasMapping;
use Illuminate\Database\Seeder;

class DinasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dinasData = [
            [
                'nama_dinas' => 'Dinas Pekerjaan Umum dan Tata Ruang (DPUTR)',
                'kode_dinas' => 'DPUTR',
                'kontak_email' => 'dputr@bandungkab.go.id',
                'kontak_telepon' => '022-5891234',
                'alamat_kantor' => 'Komplek Pemkab Bandung, Soreang',
                'kategori' => [
                    'Jalan Rusak' => 'Penanganan kerusakan jalan, jembatan berlubang, trotoar rusak, dan infrastruktur fisik jalan.',
                ],
            ],
            [
                'nama_dinas' => 'Dinas Lingkungan Hidup (DLH)',
                'kode_dinas' => 'DLH',
                'kontak_email' => 'dlh@bandungkab.go.id',
                'kontak_telepon' => '022-5891235',
                'alamat_kantor' => 'Komplek Pemkab Bandung, Soreang',
                'kategori' => [
                    'Lingkungan & Drainase' => 'Penanganan tumpukan sampah liar, saluran drainase tersumbat, banjir lokal, dan pencemaran lingkungan.',
                ],
            ],
            [
                'nama_dinas' => 'Dinas Sosial (DINSOS)',
                'kode_dinas' => 'DINSOS',
                'kontak_email' => 'dinsos@bandungkab.go.id',
                'kontak_telepon' => '022-5891236',
                'alamat_kantor' => 'Komplek Pemkab Bandung, Soreang',
                'kategori' => [
                    'Bantuan Sosial' => 'Aduan penerimaan bansos (PKH, BPNT, BLT), data DTKS tidak tepat sasaran, dan warga miskin butuh bantuan.',
                ],
            ],
            [
                'nama_dinas' => 'Satuan Polisi Pamong Praja (Satpol PP)',
                'kode_dinas' => 'SATPOL_PP',
                'kontak_email' => 'satpolpp@bandungkab.go.id',
                'kontak_telepon' => '022-5891237',
                'alamat_kantor' => 'Komplek Pemkab Bandung, Soreang',
                'kategori' => [
                    'Keamanan & Ketertiban' => 'Penanganan gangguan ketertiban umum, PKL liar, tawuran, kebisingan, dan potensi gangguan keamanan warga.',
                ],
            ],
        ];

        foreach ($dinasData as $item) {
            $dinas = Dinas::updateOrCreate(
                ['kode_dinas' => $item['kode_dinas']],
                [
                    'nama_dinas' => $item['nama_dinas'],
                    'kontak_email' => $item['kontak_email'],
                    'kontak_telepon' => $item['kontak_telepon'],
                    'alamat_kantor' => $item['alamat_kantor'],
                ]
            );

            foreach ($item['kategori'] as $kategoriNama => $deskripsi) {
                KategoriDinasMapping::updateOrCreate(
                    ['kategori' => $kategoriNama],
                    [
                        'dinas_id' => $dinas->id,
                        'deskripsi' => $deskripsi,
                    ]
                );
            }
        }
    }
}
