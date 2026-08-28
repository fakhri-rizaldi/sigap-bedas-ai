# Task Breakdown: BEDAS Lapor-AI (MVP)
### Stack: Laravel 11 + Inertia.js + Vue 3 + Gemini API

Referensi: `prd.md` (v2.0), `styleguide.md`

---

## Fase 0 — Setup Proyek

- [ ] Install Laravel 11 baru (`laravel new bedas-lapor-ai`)
- [ ] Install starter kit Inertia + Vue (`laravel new --vue` atau via Breeze/Jetstream dengan opsi Inertia+Vue)
- [ ] Setup PostgreSQL + extension PostGIS (lokal via Docker/Sail atau instalasi native)
- [ ] Konfigurasi `.env`: koneksi database, `GEMINI_API_KEY`, `APP_URL`
- [ ] Install dependency frontend: `leaflet`, `leaflet.heat`, `axios` (sudah bawaan Inertia)
- [ ] Setup Tailwind CSS + extend `tailwind.config.js` dengan token warna dari `styleguide.md`
- [ ] Setup font `Inter` & `Plus Jakarta Sans` (Google Fonts atau self-hosted)
- [ ] Inisialisasi Git repo, buat `.gitignore` standar Laravel + Node
- [ ] (Opsional) Setup Laravel Sail untuk konsistensi environment dev tim

## Fase 1 — Database & Model

- [x] Migration `aduans` table:
  - `id`, `kode_tiket` (unique, auto-generate), `teks_aduan`, `kategori`, `confidence_kategori`, `urgensi`, `dinas_tujuan`, `status` (enum: baru, diproses, selesai), `latitude`, `longitude`, `alamat`, `foto_path`, `nama_pelapor` (nullable), `kontak_pelapor` (nullable), `timestamps`
- [x] Migration `dinas` table (master data): `id`, `nama_dinas`, `kategori_mapping` (json/pivot)
- [x] Migration `kategori_dinas_mapping` (pivot table kategori → dinas, agar bisa diubah tanpa hardcode)
- [x] Model `Aduan` + relasi ke `Dinas`
- [x] Model `Dinas`
- [x] Seeder untuk 4 kategori awal + mapping dinas default
- [x] Factory `Aduan` untuk keperluan testing/dummy data

## Fase 2 — Integrasi Gemini API (Backend)

- [x] Buat `GeminiClassificationService` (class service Laravel) untuk komunikasi ke Gemini API
- [x] Rancang prompt template zero-shot classification (kategori + urgensi + format output JSON)
- [x] Buat endpoint `POST /api/aduan/classify` — terima teks, kembalikan `{kategori, confidence, urgensi, alasan}`
- [x] Implementasi error handling: timeout, response invalid JSON, rate limit tercapai
- [x] Implementasi caching sederhana (cache key = hash teks) via Laravel Cache untuk hemat kuota API
- [x] Rule-based keyword fallback untuk urgensi (sebagai lapisan tambahan/validasi silang hasil Gemini)
- [x] Unit test service klasifikasi (mock response Gemini API)
- [x] Rate limiting endpoint (`throttle` middleware) untuk cegah spam request

## Fase 3 — Form Pelaporan Publik (Frontend Vue + Inertia)

- [x] Buat halaman Inertia `Pages/Lapor/Create.vue`
- [x] Komponen textarea aduan dengan debounce (~800ms) memanggil `/api/aduan/classify`
- [x] Komponen badge kategori live (tampil confidence, warna sesuai token kategori di `styleguide.md`)
- [x] Komponen badge urgensi live
- [x] Komponen upload foto (preview, validasi tipe/ukuran file)
- [x] Ekstraksi EXIF GPS dari foto (library / client preview & geo location)
- [x] Komponen peta Leaflet untuk pin lokasi manual (fallback jika GPS tidak ada)
- [x] Integrasi reverse geocoding: panggil endpoint backend `/api/geocode` (proxy ke Nominatim) saat koordinat didapat
- [x] Validasi form sebelum submit (client-side + server-side)
- [x] State loading/skeleton saat klasifikasi & submit berlangsung
- [x] Halaman konfirmasi setelah submit (tampilkan kode tiket)
- [x] Terapkan styling sesuai `styleguide.md` (single column, mobile-first, max-width 680px)

## Fase 4 — Endpoint Geocoding (Backend)

- [x] Buat endpoint `GET /api/geocode?lat=&lng=` — proxy ke Nominatim (hindari CORS & sembunyikan rate concern dari client)
- [x] Implementasi caching hasil geocoding per koordinat (bulatkan ke presisi tertentu, misal 5 desimal)
- [x] Handle error/timeout Nominatim dengan fallback pesan "alamat tidak terdeteksi, isi manual"

## Fase 5 — Auto-Routing Tiket ke Dinas

- [x] Logic mapping kategori → dinas tujuan (baca dari tabel `kategori_dinas_mapping`, bukan hardcode)
- [x] Set `dinas_tujuan` otomatis saat tiket disimpan (`Aduan` model observer atau service saat create)
- [x] Endpoint `POST /lapor` (submit final) — simpan record lengkap + trigger routing

## Fase 6 — Event & Live Update (Reverb)

- [x] Install & konfigurasi Laravel Reverb
- [x] Buat event `AduanCreated` (broadcast on create)
- [x] Buat event `AduanStatusUpdated` (broadcast saat staf ubah status)
- [x] Setup Laravel Echo di frontend (`resources/js/echo.ts`)
- [x] Subscribe channel di dashboard Vue, update state list tiket real-time saat event masuk

## Fase 7 — Dashboard Dinas (Frontend Vue + Inertia)

- [x] Halaman Inertia `Pages/Dashboard.vue` dengan tata letak modern CRM (Split-Pane + Kanban Pipeline)
- [x] Komponen list/card tiket aduan (sesuai spesifikasi card di `styleguide.md`)
- [x] Filter: kategori, urgensi, status, instansi OPD, 31 wilayah/kecamatan
- [x] Sorting: terbaru, urgensi tertinggi dulu
- [x] Komponen detail tiket inspector — lihat foto bukti, teks lengkap, analisis AI, peta lokasi, form ubah status & catatan dinas
- [x] Layout responsif: stack di mobile, multi-kolom split-pane CRM di desktop
- [x] Integrasi live update dari Fase 6 (tiket baru muncul otomatis via Echo Reverb, badge notifikasi real-time)

## Fase 8 — Peta & Heatmap

- [x] Komponen `HeatmapMap.vue` dengan Leaflet + Leaflet.heat
- [x] Endpoint `GET /api/aduan/heatmap-data` — kembalikan array koordinat + bobot (urgensi tinggi = bobot lebih besar)
- [x] Render marker individual (klik → tampilkan detail singkat & buka di inspector) + layer heatmap (toggle on/off)
- [x] Warna marker sesuai token urgensi di `styleguide.md` (Darurat: Merah, Tinggi: Amber, Sedang: Biru, Rendah: Abu-abu)
- [x] Kontrol zoom/layer di pojok kanan bawah sesuai spesifikasi style guide
- [x] Terintegrasi pada CRM Staff Dashboard dengan mode switch (List Feed / Kanban / Peta & Heatmap)

## Fase 9 — Update Status & Portal Pelacakan Warga

- [x] Endpoint `PATCH /dashboard/aduan/{id}/status` — staf ubah status tiket & catatan dinas
- [x] Trigger `AduanStatusUpdated` event saat status berubah via WebSocket Reverb
- [x] Halaman publik `Pages/Lapor/Status.vue` — warga cek status via kode tiket (tanpa login) dengan visual stepper & live update
- [x] Integrasi link menu "Lacak Status" di navbar & tombol cepat di halaman sukses lapor

## Fase 9B — Klasifikasi Dua Lapis (Integrasi Model NLP Mandiri)

- [x] Deploy model NLP mandiri sebagai microservice kecil (FastAPI) dengan endpoint `POST /predict` — menerima teks, mengembalikan `{kategori, confidence}`
- [x] Buat `NlpValidationService` di Laravel — memanggil microservice FastAPI secara terisolasi & fail-safe
- [x] Tambah kolom di tabel `aduans`: `kategori_model_lokal`, `confidence_model_lokal`, `perlu_review` (boolean), `sumber_klasifikasi`
- [x] Buat job `ValidasiKlasifikasiJob` — memanggil model lokal, membandingkan dengan hasil Gemini, update `perlu_review` jika berbeda
- [x] Tambah fallback: jika Gemini API gagal total, gunakan hasil model lokal sebagai kategori utama dengan `sumber_klasifikasi = model_lokal`
- [x] Update komponen card tiket di dashboard (`TicketCard.vue`) — tampilkan badge `⚠️ Review` untuk tiket `perlu_review = true`
- [x] Tampilkan komparasi side-by-side Gemini vs Model Lokal di inspector (`TicketInspector.vue`)
- [x] Tambah filter dashboard: tombol pill `⚠️ Perlu Review` (memudahkan staf fokus ke aduan ambigu)
- [x] Test end-to-end: pengujian otomatis `DualLayerNlpClassificationTest.php` (5 test cases passed)

## Fase 10 — Panel Admin (Koreksi Kategori, Histori & Manajemen Dinas)

- [x] Halaman/modal admin untuk koreksi manual kategori tiket & auto-reroute dinas
- [x] Simpan histori koreksi `koreksi_kategoris` (fondasi Active Learning & audit trail)
- [x] Halaman statistik agregat analitik & performa AI (`Pages/Admin/Statistik.vue`)
- [x] Manajemen dinamis mapping kategori → dinas CRUD (`Pages/Admin/KategoriMapping.vue`)
- [x] Fitur ekspor dataset aduan & histori koreksi berformat CSV untuk retraining model NLP


## Fase 11 — Testing & QA

- [x] Unit test: `GeminiClassificationService`, `RoutingLogicTest`, `GeocodeServiceTest`, `PromptSanitizerTest`
- [x] Feature test: submit form aduan end-to-end (dengan mock Gemini API & dual-layer model)
- [x] Uji variasi teks aduan asli (bahasa Sunda, bahasa informal, typo, singkatan) — `InformalTextAccuracyTest.php`
- [x] Uji beban & ketahanan: cache hit resilience, fallback otomatis saat API LLM offline — `LoadAndRateLimitTest.php`
- [x] Python ML Pipeline Unit Tests: text preprocessing, vectorization, dan model prediction — `nlp-model/test_nlp.py`
- [x] Aksesibilitas & Responsivitas: verifikasi breakpoint 375px–1280px & kontras styleguide


## Fase 12 — Deployment & Monitoring

- [ ] Setup environment produksi (VPS/cloud, database, queue worker, Reverb server)
- [ ] Konfigurasi supervisor/systemd untuk queue worker & Reverb tetap jalan
- [ ] Setup monitoring kuota Gemini API (dashboard sederhana atau log alert saat mendekati limit)
- [ ] Setup backup database berkala
- [ ] Domain & SSL
- [ ] Dokumentasi deployment (README)

---

## Catatan Prioritas untuk MVP Minimum yang Bisa Didemokan

Jika waktu terbatas, urutan fase yang **wajib** untuk demo fungsional dasar:

1. Fase 0 (Setup)
2. Fase 1 (Database)
3. Fase 2 (Gemini API)
4. Fase 3 (Form Pelaporan)
5. Fase 4 (Geocoding)
6. Fase 5 (Auto-routing)
7. Fase 7 (Dashboard — versi sederhana tanpa live update dulu)
8. Fase 8 (Peta/heatmap — bisa versi statis dulu, load data on-refresh)

Fase 6 (Reverb/live update), Fase 9 (notifikasi), Fase 9B (Klasifikasi Dua Lapis), Fase 10 (admin panel) bisa menyusul setelah demo inti berjalan, sesuai catatan **Should/Could** di `prd.md` bagian 7.

**Catatan khusus Fase 9B**: fase ini yang menghubungkan sistem utama dengan model NLP mandiri dari `task_model_nlp.md`. Kerjakan setelah model NLP mandiri selesai dilatih & dievaluasi (bukan blocker untuk demo MVP inti), tapi penting untuk dikerjakan sebelum laporan akhir KP karena ini adalah bagian yang menunjukkan integrasi nyata antara riset model ML dan produk web yang jalan.

---

*Dokumen ini adalah breakdown teknis dari `prd.md` v2.0. Update checklist ini seiring progres pengerjaan.*
