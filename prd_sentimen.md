# PRD: BEDAS-SentimenPublik
### Analisis Kepuasan Layanan Publik / Ulasan Fasilitas

| Field | Detail |
|---|---|
| Versi | 1.0 |
| Status | Draft — untuk diusulkan ke pembimbing kerja praktik |
| Tanggal | 20 Agustus 2026 |
| Wilayah Cakupan | Fasilitas layanan publik Kab. Bandung (pilot: RSUD Soreang, Mal Pelayanan Publik, Kantor Kecamatan) |
| Stack Utama | Laravel + Inertia.js + Vue 3, Gemini API, Chart.js |

---

## 1. Latar Belakang & Masalah

Instansi pelayanan publik (RSUD, Mal Pelayanan Publik, kantor kecamatan) saat ini umumnya tidak memiliki mekanisme sistematis untuk mengukur kepuasan masyarakat secara real-time. Indeks Kepuasan Masyarakat (IKM) biasanya diukur melalui survei periodik (misal per triwulan/tahun) yang:

- **Tidak real-time** — masalah baru terdeteksi lama setelah terjadi, bukan saat kejadian.
- **Sampel terbatas** — survei periodik biasanya menjangkau sebagian kecil pengguna layanan, tidak mencerminkan pengalaman harian secara luas.
- **Tidak granular** — hasil survei formal biasanya berupa skor agregat, tanpa insight spesifik soal *apa* yang dikeluhkan (misal: antrian, sikap petugas, kebersihan fasilitas).
- **Feedback informal tersebar** — keluhan warga kerap muncul di platform lain (Google Maps, media sosial) tapi tidak pernah masuk ke sistem evaluasi resmi instansi.

## 2. Tujuan Produk

Membangun sistem feedback layanan publik berbasis web yang memungkinkan warga memberi ulasan singkat (1 menit) terhadap fasilitas yang baru mereka kunjungi, dengan **analisis sentimen otomatis** (Positif/Netral/Negatif) menggunakan Gemini API, menghasilkan **skor Indeks Kepuasan Masyarakat (IKM) instan**, dan menampilkan **kata kunci keluhan** yang paling sering muncul agar instansi dapat menindaklanjuti perbaikan layanan secara tepat sasaran.

### Keterkaitan Misi
- **Misi 3** — Pelayanan publik transparan (warga bisa memberi masukan langsung, instansi bisa memantau kepuasan secara terbuka).
- **Misi 5** — Menjaga stabilitas kepuasan masyarakat (deteksi dini penurunan kualitas layanan sebelum menjadi keluhan besar).

## 3. Mengapa Proyek Ini Lebih Sederhana dari BEDAS Lapor-AI

Dibanding Ide 1 (BEDAS Lapor-AI), proyek ini memiliki kompleksitas lebih rendah karena:

| Aspek | BEDAS Lapor-AI | BEDAS-SentimenPublik |
|---|---|---|
| Jumlah kelas klasifikasi | 4 kategori + urgensi (kompleks) | 3 kelas sentimen (Positif/Netral/Negatif) — lebih sederhana |
| Kebutuhan lokasi/geocoding | Wajib (peta, heatmap, routing dinas) | Tidak wajib (cukup pilih fasilitas dari daftar) |
| Ketersediaan dataset | Sulit (aduan spesifik lokal, tidak ada dataset publik siap pakai) | Lebih mudah (dataset sentimen Bahasa Indonesia tersedia luas untuk pretest/baseline) |
| Routing otomatis ke dinas | Wajib (bagian inti sistem) | Tidak diperlukan |
| Kompleksitas integrasi pihak ketiga | Tinggi (Nominatim, Reverb, peta) | Rendah (Chart.js untuk visualisasi, WhatsApp Gateway opsional) |

## 4. Target Pengguna

| Persona | Kebutuhan Utama |
|---|---|
| **Warga (Pemberi Ulasan)** | Memberi feedback cepat (≤1 menit) tanpa hambatan, tanpa perlu akun |
| **Admin/Kepala Instansi** | Melihat skor IKM real-time, tren kepuasan dari waktu ke waktu, kata kunci keluhan utama |
| **Petugas Layanan** | (Opsional, fase lanjutan) Melihat feedback terkait unit/lokasi kerja mereka spesifik |

## 5. Lingkup (Scope)

### 5.1 Termasuk (In-Scope — MVP)
- Form feedback publik 1-menit (pilih fasilitas + rating bintang + ulasan teks singkat opsional).
- Analisis sentimen otomatis dari teks ulasan via Gemini API (Positif/Netral/Negatif).
- Perhitungan skor IKM instan (agregat dari rating + sentimen).
- Dashboard admin: grafik pie sentimen (Chart.js), tren skor IKM dari waktu ke waktu.
- Word cloud kata kunci keluhan yang paling sering muncul (dari ulasan bersentimen negatif).
- Filter dashboard per fasilitas/lokasi & rentang waktu.

### 5.2 Tidak Termasuk (Out-of-Scope — MVP)
- Analisis berbasis aspek mendalam (Aspect-Based Sentiment Analysis) — misal memisahkan sentimen "kecepatan layanan" vs "kebersihan" secara otomatis; dicatat sebagai potensi fase lanjutan.
- Integrasi WhatsApp Gateway API (konfirmasi otomatis) — opsional, fase lanjutan, tergantung ketersediaan gateway/biaya.
- Login/akun untuk warga pemberi ulasan (feedback anonim by design).
- Moderasi otomatis ulasan spam/kasar — validasi dasar saja di MVP (rate-limiting, panjang teks minimum/maksimum).
- Integrasi langsung scraping Google Maps — dicatat sebagai sumber data pelengkap opsional, bukan bagian dari alur inti MVP.

## 6. Alur Pengguna (User Flow)

1. Warga selesai menggunakan layanan (RSUD, MPP, kantor kecamatan) → memindai QR code atau membuka link form feedback.
2. Warga memilih fasilitas/unit yang dikunjungi (dropdown atau sudah ter-preset dari QR code lokasi).
3. Warga memberi rating bintang (1-5) dan opsional menulis ulasan singkat.
4. Sistem mengirim teks ulasan ke Gemini API → mendapat label sentimen (Positif/Netral/Negatif) + skor keyakinan.
5. Data tersimpan, skor IKM fasilitas terkait ter-update otomatis.
6. Admin instansi membuka dashboard → melihat grafik pie sentimen, tren IKM, dan word cloud kata kunci keluhan terbaru.

## 7. Kebutuhan Fungsional

| ID | Kebutuhan | Prioritas |
|---|---|---|
| F1 | Form feedback publik (pilih fasilitas, rating bintang, ulasan teks opsional) | Must |
| F2 | Analisis sentimen otomatis via Gemini API | Must |
| F3 | Perhitungan skor IKM per fasilitas (agregat rating + sentimen) | Must |
| F4 | Dashboard admin dengan grafik pie sentimen (Chart.js) | Must |
| F5 | Grafik tren IKM dari waktu ke waktu (line chart) | Must |
| F6 | Word cloud kata kunci dari ulasan negatif | Should |
| F7 | Filter dashboard per fasilitas & rentang waktu | Should |
| F8 | QR code generator per fasilitas/lokasi (mempermudah akses form) | Should |
| F9 | Ekspor data laporan (CSV/PDF) untuk pelaporan formal IKM | Could |
| F10 | Integrasi WhatsApp Gateway (konfirmasi ulasan diterima) | Could |
| F11 | Panel admin: kelola daftar fasilitas/unit layanan | Must |

## 8. Kebutuhan Non-Fungsional

- **Performa**: hasil analisis sentimen tersedia < 2 detik setelah submit (tidak perlu real-time saat mengetik seperti Ide 1, karena ulasan singkat dan submit sekali).
- **Kesederhanaan UX**: form harus bisa diisi dalam ≤1 menit — idealnya 3 langkah maksimal (pilih fasilitas → rating → submit).
- **Aksesibilitas**: dioptimalkan untuk diakses via QR code dari HP, form ringan dan cepat dimuat.
- **Privasi**: ulasan anonim by default; jika ada kolom kontak (opsional), tidak ditampilkan publik.
- **Resiliensi**: jika Gemini API gagal, ulasan tetap tersimpan dengan status "belum dianalisis", diproses ulang via job queue.

## 9. Strategi Dataset & Model

Karena topik ini **jauh lebih sederhana** dari Ide 1 (hanya 3 kelas sentimen, bukan kategori spesifik teknikal), pilihan strategi dataset lebih fleksibel:

### 9.1 Pendekatan Utama (MVP): Gemini API Zero-Shot
- Sama seperti Ide 1, tidak perlu dataset training sama sekali — kirim teks ulasan ke Gemini API dengan instruksi klasifikasi 3 kelas (Positif/Netral/Negatif) + ekstraksi kata kunci keluhan.
- Cukup akurat untuk MVP, tanpa perlu proses training terpisah.

### 9.2 Dataset Publik (untuk validasi/testing, bukan kebutuhan wajib)
- Ada beberapa dataset sentimen Bahasa Indonesia yang tersedia gratis di repositori publik (kumpulan tweet berlabel sentimen positif/negatif/netral) yang bisa dipakai untuk **menguji akurasi model/prompt** sebelum go-live, meski topiknya bukan spesifik layanan publik.
- Berguna sebagai sanity check, bukan sebagai data produksi utama.

### 9.3 Google Maps Reviews (sumber data pelengkap, opsional)
- Beberapa riset akademik telah membuktikan efektivitas analisis sentimen dari ulasan Google Maps rumah sakit di Indonesia, dengan akurasi tinggi menggunakan model seperti Naive Bayes maupun BERT.
- Dapat dipertimbangkan sebagai **fitur pelengkap dashboard** di fase lanjutan (misal: "insight tambahan dari ulasan publik Google Maps"), bukan sebagai fondasi form feedback utama — karena scraping di luar API resmi berisiko melanggar Terms of Service, dan API resmi (Google Places API) memiliki keterbatasan jumlah ulasan yang bisa diambil serta biaya.
- **Rekomendasi**: tidak dijadikan bagian wajib MVP; cukup dicatat sebagai opsi pengembangan lanjutan yang perlu kajian legal/biaya lebih lanjut.

### 9.4 Data Asli dari Sistem Sendiri
- Karena sistem ini mengumpulkan feedback sejak hari pertama beroperasi, dalam beberapa bulan akan terkumpul data asli ulasan warga Kab. Bandung yang bisa dipakai untuk:
  - Validasi akurasi klasifikasi Gemini API secara berkala.
  - Bahan evaluasi jika di masa depan ingin migrasi ke model mandiri (opsional, tidak prioritas mengingat kompleksitas rendah dari 3 kelas sentimen).

## 10. Detail Integrasi Gemini API

- **Endpoint internal**: `POST /api/ulasan/analisis` → backend Laravel meneruskan teks ulasan ke Gemini API.
- **Prompt strategy**: klasifikasi 3 kelas sentimen + ekstraksi kata kunci keluhan singkat (untuk word cloud) dalam satu request, output JSON terstruktur (`sentimen`, `confidence`, `kata_kunci: []`).
- **Model**: Gemini 2.5 Flash (free tier).
- **Fallback**: jika ulasan tidak diisi (hanya rating bintang), skor sentimen diturunkan langsung dari rating (1-2 bintang = Negatif, 3 = Netral, 4-5 = Positif) tanpa perlu panggil API — menghemat kuota untuk kasus tanpa teks.

## 11. Integrasi Pihak Ketiga Lainnya

| Layanan | Fungsi | Catatan |
|---|---|---|
| Chart.js | Visualisasi grafik pie sentimen & tren IKM | Gratis, ringan, library JS standar |
| WhatsApp Gateway API | Konfirmasi ulasan diterima (opsional) | Perlu evaluasi provider (biaya, kebijakan penggunaan) — fase lanjutan |
| QR Code generator (library JS/PHP) | Generate QR per fasilitas untuk akses form | Gratis, banyak library open-source tersedia |

## 12. Metrik Keberhasilan (Success Metrics)

- **Jumlah ulasan masuk per fasilitas per bulan** — indikator adopsi warga.
- **Akurasi klasifikasi sentimen** — divalidasi berkala dengan sample manual oleh admin.
- **Waktu pengisian form** rata-rata ≤1 menit (diukur dari waktu buka form hingga submit).
- **Skor IKM per fasilitas** dapat dipantau tren-nya bulanan, menjadi dasar evaluasi kinerja layanan.

## 13. Rencana Bertahap (Phasing)

| Fase | Fokus |
|---|---|
| **Fase 1 (MVP)** | Form feedback + analisis sentimen Gemini API + dashboard dasar (pie chart + tren IKM) |
| **Fase 2** | Word cloud kata kunci, QR code generator, ekspor laporan, filter dashboard lanjutan |
| **Fase 3** | Evaluasi integrasi WhatsApp Gateway, kajian kelayakan integrasi data Google Maps sebagai insight pelengkap, potensi Aspect-Based Sentiment Analysis |

## 14. Risiko & Mitigasi

| Risiko | Mitigasi |
|---|---|
| Warga malas mengisi feedback (form dilewati) | Desain form seringkas mungkin, QR code mudah diakses di lokasi fasilitas |
| Ulasan spam/tidak relevan | Rate-limiting per sesi/IP, validasi panjang teks |
| Kuota gratis Gemini API terlampaui saat volume tinggi | Fallback skor dari rating bintang saja tanpa API call jika teks kosong; monitoring kuota |
| Word cloud menampilkan kata tidak bermakna (stopword) | Terapkan filter stopword Bahasa Indonesia sebelum render word cloud |
| Instansi kurang menindaklanjuti insight dashboard | Di luar scope teknis — perlu komitmen tata kelola dari instansi terkait (dicatat sebagai catatan non-teknis) |

## 15. Open Questions

- Apakah instansi pilot (RSUD Soreang, MPP, kantor kecamatan) sudah dikonfirmasi kesediaannya untuk pilot program ini?
- Apakah IKM yang dihasilkan sistem ini akan menggantikan atau melengkapi metode survei IKM formal yang sudah ada (biasanya mengikuti Permenpan RB)?
- Apakah perlu autentikasi/verifikasi bahwa pemberi ulasan benar-benar pernah mengunjungi fasilitas terkait (untuk mencegah ulasan palsu), atau cukup open/anonim sepenuhnya?
- Apakah integrasi WhatsApp Gateway menjadi prioritas, mengingat ada biaya provider yang perlu dipertimbangkan?

---

*Dokumen ini disiapkan sebagai bahan usulan awal untuk kerja praktik, bersamaan dengan proposal Ide 1 (BEDAS Lapor-AI).*
