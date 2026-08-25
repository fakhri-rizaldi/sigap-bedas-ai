<?php

namespace Database\Seeders;

use App\Models\Aduan;
use App\Models\Dinas;
use Illuminate\Database\Seeder;

class SampleAduanSeeder extends Seeder
{
    public function run(): void
    {
        $dputr = Dinas::where('kode_dinas', 'DPUTR')->first();
        $dlh = Dinas::where('kode_dinas', 'DLH')->first();
        $dinsos = Dinas::where('kode_dinas', 'DINSOS')->first();
        $satpolpp = Dinas::where('kode_dinas', 'SATPOLPP')->first();

        $samples = [
            [
                'teks_aduan' => 'Jalan amblas sedalam 1 meter di jalan utama Soreang dekat jembatan Cisangkuy, sangat membahayakan pengendara roda dua dan empat pada malam hari.',
                'kategori' => 'Jalan Rusak',
                'confidence_kategori' => 0.98,
                'urgensi' => 'Darurat',
                'alasan_urgensi' => 'Jalan amblas dalam berpotensi memutus akses dan menimbulkan kecelakaan fatal.',
                'dinas_id' => $dputr?->id,
                'status' => 'baru',
                'latitude' => -7.0252,
                'longitude' => 107.5197,
                'alamat' => 'Jl. Raya Soreang No. 120, Desa Soreang, Kec. Soreang, Kabupaten Bandung',
                'nama_pelapor' => 'Budi Santoso',
                'kontak_pelapor' => '08122334455',
                'email_pelapor' => 'budi.santoso@gmail.com',
                'sumber_klasifikasi' => 'gemini_api',
            ],
            [
                'teks_aduan' => 'Tumpukan sampah liar di bantaran sungai Citarum Dayeuhkolot sudah menggunung dan menimbulkan bau busuk menyengat serta menghambat aliran air.',
                'kategori' => 'Sampah & Lingkungan',
                'confidence_kategori' => 0.96,
                'urgensi' => 'Tinggi',
                'alasan_urgensi' => 'Tumpukan sampah di saluran air memperbesar risiko banjir saat hujan deras.',
                'dinas_id' => $dlh?->id,
                'status' => 'baru',
                'latitude' => -6.9839,
                'longitude' => 107.6253,
                'alamat' => 'Bantaran Sungai, Desa Dayeuhkolot, Kec. Dayeuhkolot, Kabupaten Bandung',
                'nama_pelapor' => 'Siti Nurhaliza',
                'kontak_pelapor' => '08571234567',
                'email_pelapor' => 'siti.nur@yahoo.com',
                'sumber_klasifikasi' => 'gemini_api',
            ],
            [
                'teks_aduan' => 'Ada lansia hidup sebatang kara dan sakit-sakitan di Baleendah membutuhkan bantuan sembako dan layanan kesehatan darurat.',
                'kategori' => 'Bantuan Sosial & Kemiskinan',
                'confidence_kategori' => 0.94,
                'urgensi' => 'Tinggi',
                'alasan_urgensi' => 'Kondisi lansia sakit dan membutuhkan bantuan pemenuhan kebutuhan dasar segera.',
                'dinas_id' => $dinsos?->id,
                'status' => 'diproses',
                'catatan_petugas' => 'Petugas TKSK Baleendah dan tim dinas sosial sedang meluncur ke kediaman warga.',
                'latitude' => -6.9950,
                'longitude' => 107.6335,
                'alamat' => 'Kp. Cilebak RT 02 RW 04, Kel. Baleendah, Kec. Baleendah, Kabupaten Bandung',
                'nama_pelapor' => 'Ahmad Hidayat',
                'kontak_pelapor' => '08789988776',
                'email_pelapor' => 'ahmad.hid@gmail.com',
                'sumber_klasifikasi' => 'gemini_api',
            ],
            [
                'teks_aduan' => 'PKL liar memakai seluruh bahu jalan di kawasan pasar Majalaya hingga menyebabkan kemacetan parah setiap pagi.',
                'kategori' => 'Ketertiban Umum & Linmas',
                'confidence_kategori' => 0.91,
                'urgensi' => 'Sedang',
                'alasan_urgensi' => 'Mengganggu arus lalu lintas dan hak pejalan kaki.',
                'dinas_id' => $satpolpp?->id,
                'status' => 'diproses',
                'catatan_petugas' => 'Regu patroli Satpol PP unit Majalaya telah memberikan surat imbauan penertiban.',
                'latitude' => -7.0514,
                'longitude' => 107.7567,
                'alamat' => 'Jl. Alun-Alun Majalaya, Desa Majalaya, Kec. Majalaya, Kabupaten Bandung',
                'nama_pelapor' => 'Hendri Gunawan',
                'kontak_pelapor' => '08134455667',
                'email_pelapor' => 'hendri.g@gmail.com',
                'sumber_klasifikasi' => 'gemini_api',
            ],
            [
                'teks_aduan' => 'Lampu PJU di sepanjang jalan raya Banjaran mati total sejak 3 hari lalu sehingga rawan kecelakaan dan kejahatan.',
                'kategori' => 'Jalan Rusak',
                'confidence_kategori' => 0.90,
                'urgensi' => 'Sedang',
                'alasan_urgensi' => 'Penerangan jalan minim pada malam hari.',
                'dinas_id' => $dputr?->id,
                'status' => 'selesai',
                'catatan_petugas' => 'Bohlam dan gardu PJU telah diganti oleh tim teknis DPUTR pada 21 Agustus 2026.',
                'latitude' => -7.0425,
                'longitude' => 107.5878,
                'alamat' => 'Jl. Raya Banjaran KM 14, Desa Banjaran Wetan, Kec. Banjaran, Kabupaten Bandung',
                'nama_pelapor' => 'Rina Marlina',
                'kontak_pelapor' => '08212233441',
                'email_pelapor' => 'rina.marlina@outlook.com',
                'sumber_klasifikasi' => 'rule_based_fallback',
            ],
        ];

        foreach ($samples as $item) {
            if (empty($item['kode_tiket'])) {
                $item['kode_tiket'] = Aduan::generateKodeTiket();
            }
            Aduan::create($item);
        }
    }
}
