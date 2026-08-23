<?php

namespace Database\Factories;

use App\Models\Aduan;
use App\Models\Dinas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Aduan>
 */
class AduanFactory extends Factory
{
    protected $model = Aduan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Jalan Rusak' => [
                'texts' => [
                    'Jalan raya Soreang-Banjaran banyak lubang besar dan dalam membahayakan pengendara motor.',
                    'Aspal di Jl. Raya Kopo-Sayati amblas parah, sering terjadi kecelakaan saat malam hari.',
                    'Jalan desa di Cimenyan rusak berat berbatu licin tidak bisa dilewati mobil.',
                    'Trotoar di depan pasar Soreang hancur dan berlubang membahayakan pejalan kaki.',
                ],
                'kode_dinas' => 'DPUTR',
            ],
            'Lingkungan & Drainase' => [
                'texts' => [
                    'Tumpukan sampah liar di pinggir Sungai Citarum daerah Dayeuhkolot menimbulkan bau busuk menyengat.',
                    'Drainase di kawasan Baleendah tersumbat endapan lumpur dan sampah menyebabkan banjir tiap hujan.',
                    'Saluran air di Majalaya meluap ke permukiman warga karena tanggul jebol kecil.',
                    'Banyak warga membuang limbah plastik ke selokan di RT 03 RW 05 Banjaran.',
                ],
                'kode_dinas' => 'DLH',
            ],
            'Bantuan Sosial' => [
                'texts' => [
                    'Warga lansia di Desa Bojongsoang tidak pernah menerima bantuan PKH padahal sangat membutuhkan.',
                    'Penerima bansos sembako di Baleendah dinilai tidak tepat sasaran karena banyak keluarga mampu yang dapat.',
                    'Data DTKS keluarga kami tidak terdaftar padahal kondisi ekonomi sangat sulit.',
                    'Penyaluran BLT di kantor desa mengalami pemotongan tanpa penjelasan yang jelas.',
                ],
                'kode_dinas' => 'DINSOS',
            ],
            'Keamanan & Ketertiban' => [
                'texts' => [
                    'Sering terjadi balap liar di sepanjang Jalan Soreang Terusan setiap malam Minggu pukul 01.00 WIB.',
                    'Pedagang kaki lima liar memakai seluruh badan jalan di pasar Ciparay sehingga macet total.',
                    'Sekelompok pemuda sering nongkrong sambil mabuk-mabukan di taman kota Katapang meresahkan warga.',
                    'Tawuran antar geng motor terjadi di pertigaan Margahayu semalam membawa senjata tajam.',
                ],
                'kode_dinas' => 'SATPOL_PP',
            ],
        ];

        $kategoriKey = $this->faker->randomElement(array_keys($categories));
        $kategoriInfo = $categories[$kategoriKey];
        $teksAduan = $this->faker->randomElement($kategoriInfo['texts']);
        $urgensi = $this->faker->randomElement(['Rendah', 'Sedang', 'Tinggi', 'Darurat']);
        $status = $this->faker->randomElement(['baru', 'diproses', 'selesai']);

        // Koordinat area Kabupaten Bandung (sekitar Soreang, Baleendah, Majalaya)
        // Lat: -7.05 s/d -6.95, Lng: 107.50 s/d 107.75
        $lat = $this->faker->latitude(-7.08, -6.95);
        $lng = $this->faker->longitude(107.50, 107.75);

        $dinas = Dinas::where('kode_dinas', $kategoriInfo['kode_dinas'])->first();

        return [
            'teks_aduan' => $teksAduan,
            'kategori' => $kategoriKey,
            'confidence_kategori' => $this->faker->randomFloat(4, 0.85, 0.99),
            'urgensi' => $urgensi,
            'alasan_urgensi' => 'Klasifikasi otomatis berdasarkan analisis kata kunci tingkat keparahan.',
            'dinas_id' => $dinas ? $dinas->id : null,
            'status' => $status,
            'latitude' => $lat,
            'longitude' => $lng,
            'alamat' => $this->faker->streetAddress() . ', Kab. Bandung',
            'foto_path' => null,
            'nama_pelapor' => $this->faker->name(),
            'kontak_pelapor' => $this->faker->phoneNumber(),
            'sumber_klasifikasi' => 'gemini_api',
            'perlu_review' => $this->faker->boolean(15), // 15% kemungkinan perlu review
            'kategori_model_lokal' => $kategoriKey,
            'confidence_model_lokal' => $this->faker->randomFloat(4, 0.80, 0.98),
            'catatan_petugas' => null,
        ];
    }
}
