# PRD: BEDAS Lapor-AI
### Klasifikasi Otomatis Aduan Fasilitas Lingkungan & Analisis Sentimen

| Field | Detail |
|---|---|
| Versi | 2.1 (revisi — menambahkan fitur Klasifikasi Dua Lapis) |
| Status | Draft |
| Tanggal | 22 Agustus 2026 |
| Wilayah Cakupan | Kota/Kabupaten Bandung (pilot area — fokus MVP) |
| Stack Utama | Laravel + Inertia.js + Vue 3, Gemini API, Model NLP Mandiri (TF-IDF + SVM) |

---

## 1. Latar Belakang & Masalah

Saat ini, aduan warga terkait fasilitas lingkungan (jalan rusak, sampah, banjir, keamanan) diproses secara manual: staf dinas harus membaca satu per satu, menentukan kategori, menilai urgensi, lalu meneruskan ke dinas teknis terkait. Proses ini menyebabkan:

- **Keterlambatan respons** karena antrian sortir manual.
- **Kesalahan klasifikasi** akibat human error atau interpretasi subjektif staf.
- **Tidak ada visibilitas spasial** — dinas kesulitan melihat pola/klaster masalah di suatu wilayah tanpa rekap manual.
- **Prioritas tidak konsisten** — aduan darurat bisa tertunda karena tercampur dengan aduan non-urgent dalam antrian yang sama.

## 2. Tujuan Produk

Membangun sistem pelaporan aduan berbasis web yang **otomatis mengklasifikasikan** isi aduan warga (kategori & urgensi) menggunakan kombinasi **Gemini API** dan **model NLP mandiri (TF-IDF + SVM)** yang saling memvalidasi ("Klasifikasi Dua Lapis"), **mendeteksi lokasi** secara otomatis dari koordinat GPS, dan **merutekan tiket** langsung ke dinas terkait tanpa sortir manual — didukung dashboard prioritas dan peta klaster (heatmap) untuk pengambilan keputusan dinas.

### Keterkaitan Misi
- **Misi 3** — Tata kelola partisipatif (warga terlibat aktif melapor, transparansi status tiket).
- **Misi 4** — Infrastruktur berwawasan lingkungan (deteksi cepat isu lingkungan/drainase).
- **Misi 5** — Stabilitas ketertiban umum (prioritas otomatis untuk isu keamanan/darurat).

## 3. Keputusan Stack Teknis (MVP)

Klasifikasi kategori & urgensi dilakukan melalui **kombinasi dua model ("Klasifikasi Dua Lapis")**: Gemini API (zero-shot) sebagai model utama, dan model NLP mandiri (TF-IDF + SVM, dilatih dari dataset sintetis — lihat `prd_model_nlp.md`) sebagai lapisan validasi silang. Detail lengkap fitur ini ada di Bagian 9.4.

| Layer | Teknologi | Alasan |
|---|---|---|
| Backend & routing | **Laravel 11** | Familiar bagi tim, ekosistem matang, satu repo untuk backend + frontend |
| Frontend interaktif | **Inertia.js + Vue 3** | SPA-feel tanpa membangun REST API terpisah; kebutuhan dashboard filter/chart yang kaya cocok dengan reactivity Vue |
| Klasifikasi teks — model utama | **Gemini API (Gemini 2.5 Flash)** | Tidak perlu dataset training; free tier cukup besar; mendukung multimodal (bisa dipakai untuk analisis foto juga) |
| Klasifikasi teks — model validasi | **Model NLP mandiri (TF-IDF + SVM)** | Offline, tanpa biaya API, dilatih dari dataset sintetis; jadi lapisan kedua untuk validasi silang & fallback |
| Live update dashboard | **Laravel Reverb + Laravel Echo** | WebSocket native Laravel, gratis, self-hosted |
| Peta & heatmap | **Leaflet.js + Leaflet.heat** | Ringan, gratis, tanpa API key |
| Reverse geocoding | **Nominatim (OpenStreetMap)** | Gratis, sesuai rencana awal |
| Database | **PostgreSQL + PostGIS** | Query spasial (klaster per wilayah, radius) |
| Storage foto | **Local storage (MVP) → S3-compatible (produksi)** | MVP cukup local disk Laravel, upgrade saat scale |

### Alasan Memilih Inertia + Vue (bukan Livewire)
Dashboard prioritas aduan membutuhkan interaksi kaya (filter multi-kondisi, peta interaktif, live update tanpa reload) yang lebih natural dibangun dengan komponen Vue reaktif dibanding round-trip server penuh ala Livewire. Form pelaporan publik tetap sederhana dan ringan meski dibangun dengan Vue, karena hanya satu halaman dengan sedikit state.

## 4. Target Pengguna

| Persona | Kebutuhan Utama |
|---|---|
| **Warga (Pelapor)** | Melapor cepat, tanpa perlu tahu harus lapor ke dinas mana; melihat status tiketnya |
| **Staf Dinas (Operator)** | Melihat tiket masuk sudah terklasifikasi & terprioritisasi, tanpa sortir manual |
| **Pimpinan Dinas / Pengambil Keputusan** | Melihat dashboard agregat — klaster masalah, tren, beban kerja per wilayah |
| **Admin Sistem** | Mengelola mapping kategori→dinas, memantau akurasi klasifikasi Gemini, koreksi manual jika perlu |

## 5. Lingkup (Scope)

### 5.1 Termasuk (In-Scope — MVP)
- Form pelaporan publik (teks bebas + upload foto + lokasi GPS/manual pin) — Vue component via Inertia.
- Klasifikasi otomatis kategori aduan (real-time saat mengetik, debounced call ke Gemini API, dengan confidence score).
- **Klasifikasi Dua Lapis** — validasi silang hasil Gemini API dengan model NLP mandiri (TF-IDF+SVM); tiket ditandai "perlu review" jika kedua model tidak sepakat.
- Deteksi tingkat urgensi (High/Normal) via Gemini API + rule-based keyword sebagai lapisan tambahan.
- Reverse geocoding otomatis (koordinat → alamat jalan/kecamatan) via Nominatim.
- Auto-routing tiket ke dinas tujuan berdasarkan kategori.
- Dashboard prioritas aduan untuk staf dinas (list + filter status/kategori/urgensi) — Vue component.
- Peta sebaran klaster aduan (heatmap).
- Update status tiket oleh staf dinas, notifikasi ke pelapor.
- Live update dashboard (tiket baru muncul tanpa refresh) via Reverb.

### 5.2 Tidak Termasuk (Out-of-Scope — MVP)
- Aplikasi mobile native (fokus web-responsive dulu).
- Model ML mandiri (TF-IDF/SVM/fine-tuned) — menyusul di fase lanjutan setelah data asli terkumpul.
- Analisis sentimen lanjutan (nada emosi/kepuasan warga).
- Integrasi pembayaran/bansos langsung.
- Chatbot percakapan dua arah dengan warga.
- Multi-bahasa daerah selain Bahasa Indonesia.

## 6. Alur Pengguna (User Flow)

1. Warga membuka form pelaporan (halaman Vue via Inertia) → mengetik keluhan bebas.
2. Saat mengetik (debounced ~800ms), frontend memanggil endpoint Laravel `/api/classify` → backend meneruskan ke Gemini API → kategori & confidence tampil real-time di form (contoh: *Lingkungan & Drainase — 94%*). Badge yang tampil ke warga tetap berdasarkan hasil Gemini API saja (real-time, tidak menunggu model kedua) agar UX tetap cepat.
3. Warga upload foto → backend membaca EXIF GPS (atau warga pin manual di peta Leaflet) → reverse geocoding via Nominatim mengisi alamat otomatis.
4. Warga submit → di backend, teks aduan **juga** dikirim ke model NLP mandiri secara paralel/asinkron untuk validasi silang (lihat Bagian 9.4) → tiket dibuat dengan status **Baru**, kategori, urgensi, dan dinas tujuan sudah terisi otomatis berdasarkan hasil Gemini API; flag `perlu_review` diisi jika kedua model tidak sepakat.
5. Event `AduanCreated` di-broadcast via Reverb → tiket langsung tampil di dashboard dinas terkait (real-time, tanpa refresh) dan muncul di peta heatmap. Tiket dengan flag `perlu_review` ditandai secara visual berbeda di dashboard.
6. Staf dinas menindaklanjuti → mengubah status (Diproses/Selesai), atau mengoreksi kategori jika tiket ditandai perlu review → warga menerima notifikasi.

## 7. Kebutuhan Fungsional

| ID | Kebutuhan | Prioritas |
|---|---|---|
| F1 | Form input teks aduan dengan klasifikasi kategori real-time (via Gemini API) | Must |
| F2 | Deteksi urgensi (High/Normal) berbasis Gemini API + rule keyword fallback | Must |
| F3 | Upload foto dengan ekstraksi EXIF GPS | Must |
| F4 | Reverse geocoding otomatis (Nominatim) → isi alamat | Must |
| F5 | Fallback pin lokasi manual di peta (Leaflet) jika GPS tidak tersedia | Must |
| F6 | Auto-routing tiket ke dinas berdasarkan mapping kategori | Must |
| F7 | Dashboard daftar tiket (Vue) dengan filter (kategori, urgensi, status, wilayah) | Must |
| F8 | Peta heatmap sebaran klaster aduan | Must |
| F9 | Live update tiket baru di dashboard via Reverb (tanpa refresh) | Should |
| F10 | Update status tiket oleh staf + notifikasi ke pelapor | Should |
| F11 | Halaman cek status tiket untuk warga (tanpa login) | Should |
| F12 | Panel admin: koreksi manual kategori (data disimpan untuk training model masa depan) | Could |
| F13 | Statistik agregat (jumlah aduan per kategori/wilayah/waktu) | Could |
| F14 | Caching hasil klasifikasi Gemini untuk teks identik/mirip (hemat kuota API) | Should |
| F15 | Klasifikasi Dua Lapis — validasi silang Gemini API vs model NLP mandiri, flag `perlu_review` jika berbeda | Should |
| F16 | Indikator visual di dashboard untuk tiket berstatus `perlu_review` | Should |
| F17 | Model NLP mandiri sebagai fallback klasifikasi saat Gemini API gagal/limit habis | Could |

## 8. Kebutuhan Non-Fungsional

- **Performa**: hasil klasifikasi real-time tampil < 1.5 detik setelah warga berhenti mengetik (termasuk round-trip ke Gemini API).
- **Ketersediaan**: sistem dapat diakses 24/7, uptime target 99%.
- **Skalabilitas**: mampu menangani lonjakan aduan saat kejadian darurat (misal musim hujan/banjir); perlu strategi rate-limit & antrian jika kuota Gemini API tergores.
- **Keamanan**: validasi upload foto (tipe & ukuran file), rate-limiting form submission untuk cegah spam, API key Gemini disimpan di `.env` backend (tidak pernah dikirim ke frontend).
- **Privasi**: data lokasi & foto warga hanya diakses oleh dinas terkait, bukan publik penuh.
- **Aksesibilitas**: form dapat digunakan dari perangkat mobile dengan koneksi terbatas; sesuai `styleguide.md`.
- **Resiliensi terhadap API pihak ketiga**: jika Gemini API gagal/limit habis, sistem tetap menerima laporan dengan status "menunggu klasifikasi" (fallback graceful, bukan block submission).

## 9. Detail Integrasi Gemini API

### 9.1 Klasifikasi Kategori & Urgensi
- **Endpoint internal**: `POST /api/aduan/classify` (Laravel) → memanggil Gemini API dari backend (bukan langsung dari frontend, untuk menyembunyikan API key).
- **Prompt strategy**: zero-shot classification — kirim teks aduan + daftar kategori tetap (Jalan Rusak, Lingkungan & Drainase, Bansos, Keamanan & Ketertiban) + instruksi format output JSON (kategori, confidence, urgensi, alasan singkat).
- **Model**: Gemini 2.5 Flash (free tier, cukup cepat untuk kebutuhan real-time).
- **Rate limit awareness**: implementasi debounce di frontend (jangan panggil API setiap keystroke) + caching per sesi (jangan re-classify teks yang sama berulang).

### 9.2 Analisis Foto (opsional, fase lanjutan)
- Gemini mendukung multimodal — foto laporan dapat dikirim bersama teks untuk verifikasi tambahan (contoh: memastikan foto benar-benar menunjukkan jalan rusak, bukan foto tidak relevan).
- Dicatat sebagai peningkatan Fase 2, tidak wajib di MVP awal.

### 9.3 Fallback & Error Handling
- Jika Gemini API timeout/limit tercapai → tiket tetap disimpan dengan kategori "Belum Terklasifikasi", masuk antrian retry job (Laravel Queue).
- Staf dinas dapat mengklasifikasi manual dari dashboard sebagai fallback.

### 9.4 Klasifikasi Dua Lapis (Gemini API + Model NLP Mandiri)

**Latar belakang**: selain sebagai lapisan reliabilitas produksi, fitur ini juga menjadi wadah untuk model NLP mandiri (TF-IDF + SVM, dilatih dari dataset sintetis — lihat `prd_model_nlp.md` dan `task_model_nlp.md`) supaya tetap terpakai nyata dalam sistem, bukan sekadar eksperimen terpisah yang tidak terhubung ke produk.

**Cara kerja:**
1. Saat warga submit form (bukan saat real-time mengetik — supaya tidak membebani model kedua di setiap keystroke), backend memanggil **dua model secara paralel**:
   - Gemini API → hasil ditampilkan ke warga secara real-time (tetap seperti alur di Bagian 9.1).
   - Model NLP mandiri (dipanggil via microservice FastAPI internal atau proses Python terpisah) → berjalan asinkron di background, tidak menghambat submit warga.
2. Backend membandingkan hasil kedua model:
   - **Sepakat** (kategori sama) → kategori final memakai hasil Gemini API, `perlu_review = false`.
   - **Tidak sepakat** → kategori final tetap memakai hasil Gemini API (karena umumnya lebih akurat untuk bahasa informal), tapi `perlu_review = true` dan tiket ditandai untuk verifikasi staf dinas.
3. Staf dinas melihat tiket bertanda `perlu_review` di dashboard (badge/warna berbeda) dan dapat mengoreksi kategori jika perlu.
4. Data hasil review staf disimpan sebagai bahan evaluasi & potensi retraining model NLP mandiri di masa depan.

**Endpoint tambahan**: `POST /api/aduan/validate-classification` (dipanggil backend secara internal, bukan dari frontend) — mengirim teks ke model NLP mandiri, mengembalikan `{kategori, confidence}` untuk dibandingkan dengan hasil Gemini API.

**Fallback tambahan**: jika Gemini API gagal total (bukan sekadar tidak sepakat, tapi error/limit habis), sistem otomatis memakai hasil model NLP mandiri sebagai kategori sementara, dengan flag `sumber_klasifikasi = "model_lokal"` agar staf tahu ini bukan hasil Gemini API.

**Nilai tambah:**
- **Produk**: meningkatkan reliabilitas klasifikasi (dua sumber independen saling mengecek) dan memberi jalur cadangan saat Gemini API bermasalah.
- **Akademik (untuk laporan KP)**: menghasilkan data perbandingan nyata antara pendekatan LLM zero-shot vs model klasik terlatih — berapa persen kasus sepakat/tidak sepakat, pola kesalahan yang muncul — yang bisa dianalisis dan dilaporkan sebagai bagian evaluasi.

## 10. Integrasi Pihak Ketiga Lainnya

| Layanan | Fungsi | Catatan |
|---|---|---|
| Nominatim (OpenStreetMap) | Reverse geocoding koordinat → alamat | Rate limit 1 req/detik di server publik; pertimbangkan caching per koordinat |
| Leaflet.js + Leaflet.heat | Visualisasi peta & heatmap | Gratis, ringan, tanpa API key |
| Laravel Reverb | WebSocket untuk live update | Self-hosted, gratis, bagian ekosistem Laravel resmi |

## 11. Metrik Keberhasilan (Success Metrics)

- **Akurasi klasifikasi kategori** ≥ 85% dibanding label manual staf (diukur pada sample uji setelah 1 bulan berjalan).
- **Waktu rata-rata dari submit → tiket sampai ke dinas tujuan** turun signifikan dibanding proses manual (target: < 5 menit vs. proses manual yang bisa berjam-jam/berhari-hari).
- **Persentase tiket yang perlu dikoreksi manual oleh staf** < 15% setelah 3 bulan berjalan.
- **Kuota Gemini API terpakai** tetap dalam batas free tier selama masa pilot (dipantau via dashboard admin).
- **Adopsi**: jumlah aduan masuk per bulan meningkat dibanding kanal lama.

## 12. Rencana Bertahap (Phasing)

| Fase | Fokus |
|---|---|
| **Fase 1 (MVP)** | Form pelaporan (Inertia+Vue) + klasifikasi via Gemini API + geocoding + dashboard list + auto-routing |
| **Fase 2** | Heatmap, live update (Reverb), notifikasi status ke warga, panel koreksi admin, **integrasi Klasifikasi Dua Lapis (model NLP mandiri sebagai validasi silang)** |
| **Fase 3** | Retraining model NLP mandiri dengan data asli terkumpul + hasil koreksi staf, evaluasi apakah model lokal bisa menjadi model utama untuk mengurangi ketergantungan pada API eksternal & kuota gratis |

## 13. Risiko & Mitigasi

| Risiko | Mitigasi |
|---|---|
| Kuota gratis Gemini API berubah/dipangkas sewaktu-waktu | Implementasi fallback rule-based sederhana, monitoring kuota, siapkan budget kecil sebagai cadangan |
| Akurasi klasifikasi zero-shot tidak konsisten untuk bahasa informal/typo warga | Uji prompt dengan berbagai gaya bahasa aduan asli sebelum go-live, sediakan koreksi manual staf |
| Rate limit Nominatim menghambat saat volume tinggi | Cache hasil geocoding per koordinat, pertimbangkan self-host jika perlu |
| Warga menyalahgunakan form (spam/laporan palsu) | Rate-limiting, validasi foto, opsional captcha |
| Ketergantungan pada GPS foto yang tidak selalu akurat | Sediakan fallback pin manual di peta |
| Latency Gemini API mengganggu UX real-time saat mengetik | Debounce agresif, tampilkan skeleton/loading state yang jelas, jangan block submit menunggu klasifikasi selesai |
| Model NLP mandiri (dilatih dari data sintetis) memberi hasil validasi yang kurang akurat | Model kedua hanya berperan sebagai *flag* untuk review, bukan penentu kategori final — dampak kesalahan model kedua terbatas pada penambahan beban review staf, bukan salah routing tiket |
| Memanggil dua model menambah beban komputasi/latency backend | Model NLP mandiri dipanggil asinkron setelah submit (bukan real-time saat mengetik), tidak menghambat UX warga |

## 14. Open Questions

- Apakah tersedia budget cadangan jika kuota gratis Gemini API terlampaui saat pilot berjalan?
- Apakah warga perlu login/akun, atau pelaporan anonim dengan nomor tiket saja?
- Siapa yang menentukan mapping final kategori → dinas tujuan (perlu validasi dari masing-masing dinas)?
- Apakah ada SLA respons yang harus dipenuhi dinas per kategori/urgensi?
- Apakah permohonan dataset ke Open Data Jabar/Bandung (dibahas sebelumnya) tetap dijalankan paralel untuk persiapan Fase 3?

## 15. Dokumen Terkait

- `styleguide.md` — panduan desain visual & UI.
- `task.md` — breakdown implementasi teknis sistem utama.
- `prd_model_nlp.md` — PRD pembangunan model NLP mandiri (TF-IDF+SVM) yang dipakai di fitur Klasifikasi Dua Lapis (Bagian 9.4).
- `task_model_nlp.md` — breakdown implementasi model NLP mandiri.
- `dataset_aduan.csv` — dataset sintetis untuk melatih model NLP mandiri.

---

*Dokumen ini adalah revisi dari draft sebelumnya, disesuaikan dengan keputusan stack teknis Laravel + Inertia + Vue, Gemini API, dan penambahan fitur Klasifikasi Dua Lapis dengan model NLP mandiri.*
