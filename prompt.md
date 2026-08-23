# Instruksi Proyek: BEDAS Lapor-AI (Ekosistem Lengkap)

Kamu adalah asisten development untuk proyek kerja praktik ini. Sebelum mengerjakan apa pun, baca dan pahami seluruh konteks di bawah — proyek ini terdiri dari **dua ide produk** dan **satu sub-proyek riset model ML**, yang saling terhubung.

## Struktur Dokumen — Baca Berurutan

### 1. Produk Utama: BEDAS Lapor-AI
- **`prd.md`** (v2.1) — PRD utama. Sistem pelaporan aduan warga (Jalan Rusak, Sampah/Banjir, Bansos, Keamanan/Ketertiban) dengan klasifikasi otomatis, geocoding, auto-routing ke dinas, dashboard, dan peta heatmap.
- **`styleguide.md`** — panduan desain visual & UI (warna, tipografi, komponen), mengikuti gaya website resmi pemerintah, wajib diikuti persis untuk semua styling.
- **`task.md`** — breakdown implementasi 12+ fase untuk sistem utama ini.

### 2. Sub-Proyek: Model NLP Mandiri (bagian dari BEDAS Lapor-AI)
- **`prd_model_nlp.md`** — PRD untuk membangun model klasifikasi teks NLP mandiri (TF-IDF + SVM/Naive Bayes) dari dataset sintetis, karena tidak ada dataset publik yang cocok untuk domain ini.
- **`task_model_nlp.md`** — breakdown 11 fase: setup, preprocessing, training, evaluasi, ekspor model.
- **`dataset_aduan.csv`** — dataset sintetis buatan (181 baris, 4 kategori seimbang) untuk melatih model ini. **Catatan penting**: ini data sintetis/buatan, bukan data aduan asli warga — harus disebutkan sebagai limitasi di laporan KP.

### 3. Ide Alternatif (diusulkan paralel ke pembimbing): BEDAS-SentimenPublik
- **`prd_sentimen.md`** — PRD untuk sistem analisis kepuasan layanan publik (form feedback 1 menit, analisis sentimen Positif/Netral/Negatif, skor IKM, word cloud keluhan). Proyek ini **terpisah** dari BEDAS Lapor-AI, diusulkan sebagai opsi kedua ke pembimbing kerja praktik. Jangan campur implementasinya dengan BEDAS Lapor-AI kecuali diminta eksplisit.

## Keputusan Kunci yang Sudah Diambil (Jangan Diubah Tanpa Konfirmasi)

- **Stack**: Laravel 11 + Inertia.js + Vue 3 + Tailwind CSS, PostgreSQL + PostGIS, Gemini API (2.5 Flash), Leaflet.js + Leaflet.heat, Nominatim, Laravel Reverb.
- **Environment lokal**: Laravel Herd (Windows), database PostgreSQL sudah terhubung dan berfungsi.
- **Fitur "Klasifikasi Dua Lapis"** (PRD Bagian 9.4) — ini fitur penting yang menghubungkan produk utama dengan sub-proyek model NLP:
  - Gemini API = model utama, hasilnya yang ditampilkan real-time ke warga saat mengetik dan dipakai untuk auto-routing.
  - Model NLP mandiri = model validasi, dipanggil **asinkron setelah submit** (bukan real-time, supaya tidak menghambat UX form).
  - Jika kedua model tidak sepakat kategori → tiket ditandai `perlu_review = true`, staf dinas mengecek manual.
  - Jika Gemini API gagal total → model NLP mandiri jadi fallback sementara.
- **Auth**: Laravel built-in authentication (bukan WorkOS), dengan Registration + Password confirmation aktif; Email verification, 2FA, Passkeys sengaja dimatikan untuk MVP.
- **API key Gemini** hanya boleh diakses dari backend Laravel, tidak pernah dari frontend Vue.

## Status Progres Saat Ini

- ✅ Project Laravel + Vue (starter kit Inertia) sudah ter-install via Herd.
- ✅ Database PostgreSQL (`bedas_lapor_ai`) sudah terhubung dan migration dasar (users, sessions, dll) sudah jalan.
- ✅ Dataset sintetis untuk model NLP mandiri sudah dibuat (`dataset_aduan.csv`).
- ⬜ Belum dimulai: migration tabel domain (`aduans`, `dinas`), integrasi Gemini API, form pelaporan, training model NLP mandiri, dan seluruh fase lain di kedua `task.md`.

## Aturan Kerja

- **Rujuk dokumen, jangan berasumsi.** Kalau ada detail yang tidak tercakup di dokumen-dokumen ini, tanyakan dulu sebelum membuat keputusan sepihak yang berdampak luas.
- **Style guide bersifat mengikat** — gunakan token warna, spacing, dan spesifikasi komponen dari `styleguide.md` persis, bukan preferensi sendiri.
- **Checklist di kedua `task.md` adalah acuan progres** — ikuti urutan fase, terutama dependency inti (database & Gemini service) sebelum fitur lanjutan.
- **Jangan campur BEDAS Lapor-AI dengan BEDAS-SentimenPublik** — keduanya proyek terpisah meski berbagi beberapa prinsip desain (styleguide bisa dipakai ulang, tapi model data dan alurnya beda).
- **Model NLP mandiri**: ingat ini dilatih dari data sintetis. Kalau diminta melaporkan/menjelaskan akurasi model, selalu sertakan catatan bahwa ini perlu divalidasi ulang dengan data asli begitu sistem berjalan.
- **Prioritas MVP**: kalau waktu terbatas, ikuti urutan "MVP Minimum yang Bisa Didemokan" di akhir `task.md` (sistem utama) — fitur Klasifikasi Dua Lapis (Fase 9B) dan model NLP mandiri boleh menyusul setelah demo inti (form → klasifikasi Gemini → dashboard → peta) berjalan.

## Mulai Dari Mana

Progres terakhir ada di **Fase 1 — Database & Model** (`task.md` sistem utama): buat migration `aduans` dan `dinas` sesuai skema di `prd.md` Bagian 7 (kebutuhan fungsional) dan struktur yang disebutkan di alur Bagian 6.

Untuk sub-proyek model NLP, bisa dikerjakan paralel mengikuti `task_model_nlp.md` mulai dari Fase 0 (setup Python environment) — tidak saling bergantung dengan pengerjaan Laravel di awal, baru terhubung nanti di Fase 9B (`task.md`) setelah model NLP selesai dilatih.

---

Sebelum mulai menulis kode, konfirmasi bahwa kamu sudah membaca dan memahami seluruh dokumen di atas, lalu ringkas dalam 4-5 kalimat: (1) apa yang akan kamu kerjakan pertama kali, dan (2) apakah ada asumsi yang perlu dikonfirmasi dulu ke saya sebelum mulai.
