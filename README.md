# 🏛️ SIGAP BEDAS Lapor-AI — Technical Handover & Engineering Guide

> **Sistem Informasi & Gerak Aduan Publik Cerdas Kabupaten Bandung Berbasis Kecerdasan Buatan Ganda (*Dual-Layer AI: Google Gemini + Independent Local NLP Model*)**  
> *Dokumentasi teknis resmi untuk tim Software Engineer, Machine Learning Engineer, dan DevOps yang akan melanjutkan pemeliharaan, pengembangan fitur, maupun deployment sistem ini.*

---

## 📌 Daftar Isi
1. [Arsitektur Sistem & Alur Data](#-arsitektur-sistem--alur-data)
2. [Teknologi & Dependensi (*Tech Stack*)](#-teknologi--dependensi-tech-stack)
3. [Struktur Direktori Proyek (*Repository Anatomy*)](#-struktur-direktori-proyek-repository-anatomy)
4. [Prasyarat Lingkungan (*Prerequisites*)](#-prasyarat-lingkungan-prerequisites)
5. [Panduan Instalasi & Setup Lokal (*Local Setup Guide*)](#-panduan-instalasi--setup-lokal-local-setup-guide)
6. [Menjalankan Sistem di Lingkungan Development](#-menjalankan-sistem-di-lingkungan-development)
7. [Arsitektur Machine Learning & Retraining Workflow (*Active Learning*)](#-arsitektur-machine-learning--retraining-workflow-active-learning)
8. [Daftar Endpoint API & Routing Kunci](#-daftar-endpoint-api--routing-kunci)
9. [Pengujian Otomatis & Standar Keamanan (*Testing & QA*)](#-pengujian-otomatis--standar-keamanan-testing--qa)
10. [Panduan Deployment Production & Server Hardening](#-panduan-deployment-production--server-hardening)
11. [Troubleshooting & Solusi Kendala Umum](#-troubleshooting--solusi-kendala-umum)

---

## 🏗️ Arsitektur Sistem & Alur Data

Aplikasi ini menggunakan paradigma **Dual-Layer Artificial Intelligence** untuk menjamin akurasi tinggi, kecepatan respons, dan toleransi kegagalan (*fault tolerance*):

```
                        ┌──────────────────────────────────────────────┐
                        │           WARGA / LAPORAN PUBLIK             │
                        │    (Form Web + Pinpoint Map + Foto Canvas)   │
                        └──────────────────────┬───────────────────────┘
                                               │ POST /lapor
                                               ▼
                        ┌──────────────────────────────────────────────┐
                        │          LARAVEL BACKEND GATEWAY             │
                        │    - Anti-Prompt Injection Filter            │
                        │    - Client-side Canvas Image Storage        │
                        │    - Coordinate Boundary Check (Kab. Bandung)│
                        └──────────────┬────────────────┬──────────────┘
                                       │                │
             [ Layer 1: Cloud LLM ]    │                │  [ Layer 2: Local ML ]
         ┌─────────────────────────────▼─┐            ┌─▼───────────────────────────┐
         │       Google Gemini API       │            │  FastAPI NLP Microservice   │
         │   (gemini-3.5-flash-lite)     │            │  (TF-IDF + Calibrated SVM)  │
         │   Konteks semantik & urgensi  │            │  Inference < 10ms (Offline) │
         └──────────────┬────────────────┘            └─┬───────────────────────────┘
                        │                               │
                        └──────────────┬────────────────┘
                                       ▼
                        ┌──────────────────────────────────────────────┐
                        │         CROSS-VALIDATION EVALUATOR           │
                        │  - Sepakat (Gemini == Local)  ──► Auto-Route │
                        │  - Berbeda / Conf < 0.70      ──► ⚠️ Review │
                        └──────────────────────┬───────────────────────┘
                                               │
                        ┌──────────────────────▼───────────────────────┐
                        │        OPD DINAS & STAFF CRM BOARD           │
                        │    - Real-Time Live Sync (Laravel Reverb)    │
                        │    - Kanban & Heatmap Sebaran Aduan          │
                        │    - Manual Category Correction & Audit Log  │
                        └──────────────────────┬───────────────────────┘
                                               │
                        ┌──────────────────────▼───────────────────────┐
                        │           ACTIVE LEARNING FEEDBACK           │
                        │   Data koreksi staf diekspor ke CSV untuk    │
                        │   retraining berkala model NLP mandiri       │
                        └──────────────────────────────────────────────┘
```

---

## 💻 Teknologi & Dependensi (*Tech Stack*)

### 1. Backend Core & API
- **Framework:** Laravel 12 (PHP 8.2+)
- **Database:** SQLite (Development) / PostgreSQL atau MariaDB (Production)
- **Real-Time WebSocket:** Laravel Reverb (`php artisan reverb:start`)
- **HTTP Client:** Guzzle dengan isolasi `connectTimeout` ketat (1–3 detik)

### 2. Frontend & User Interface
- **Framework:** Vue 3 (Composition API dengan `<script setup lang="ts">`)
- **Routing & State Glue:** Inertia.js (Single Page Application tanpa reload)
- **Styling:** Tailwind CSS v4 & Lucide Vue Icons
- **Peta & GIS:** Leaflet JS + OpenStreetMap Standard Basemap (100% bebas watermark) + Leaflet Heatmap Layer

### 3. Sub-Proyek Machine Learning Mandiri (`nlp-model/`)
- **Runtime:** Python 3.10+
- **Microservice Web Server:** FastAPI + Uvicorn (`http://127.0.0.1:8001`)
- **Library ML:** Scikit-Learn, Pandas, NumPy, Joblib
- **Algoritma:** TF-IDF Vectorizer (Unigram + Bigram, 5.000 max features) + `CalibratedClassifierCV(LinearSVC)` dengan 5-fold CV score 99.08%.

---

## 📁 Struktur Direktori Proyek (*Repository Anatomy*)

```
bedas-lapor-ai/
├── app/
│   ├── Http/Controllers/
│   │   ├── AduanPublicController.php       # Form lapor warga & tracking publik
│   │   ├── DashboardController.php         # CRM Staf, filter tiket, update status, koreksi
│   │   ├── Admin/
│   │   │   ├── KategoriMappingController.php # CRUD pemetaan kategori ke instansi OPD
│   │   │   └── StatistikController.php     # Agregasi analitik & ekspor dataset CSV
│   │   └── Api/
│   │       ├── AduanClassificationController.php # Live AI auto-classify endpoint
│   │       ├── HeatmapController.php       # Data geospasial sebaran aduan
│   │       └── GeocodeController.php       # Reverse geocoding & boundary check
│   ├── Models/
│   │   ├── Aduan.php                       # Model utama laporan aduan warga
│   │   ├── Dinas.php                       # Master instansi OPD (DPUTR, DLH, DINSOS, Satpol PP)
│   │   ├── KategoriDinasMapping.php        # Relasi dinamis Kategori -> Dinas
│   │   └── KoreksiKategori.php             # Log histori koreksi kategori (Active Learning)
│   ├── Services/
│   │   ├── GeminiClassificationService.php # Klien Google Gemini dengan sandbox Anti-Injection
│   │   ├── NlpValidationService.php        # Klien HTTP fail-safe ke microservice FastAPI
│   │   └── RuleBasedClassificationService.php # Fallback engine berbasis aturan kata kunci
│   └── Events/
│       └── AduanStatusUpdated.php          # Event siaran WebSocket Reverb saat status berubah
├── nlp-model/
│   ├── app.py                              # Entrypoint microservice FastAPI (port 8001)
│   ├── train.py                            # Script pipeline training, evaluasi & export model .pkl
│   ├── test_nlp.py                         # Unit test ML Python
│   ├── data/
│   │   └── dataset_aduan_1200.csv          # 1.200 dataset sintetis berlabel seimbang
│   ├── models/
│   │   ├── svm_model.pkl                   # Model Calibrated Linear SVM terserialisasi
│   │   └── tfidf.pkl                       # Vectorizer TF-IDF terserialisasi
│   ├── notebooks/
│   │   └── eksplorasi_dan_training.ipynb   # Jupyter Notebook untuk EDA & evaluasi grafik
│   └── src/
│       ├── preprocessing.py                # Regex cleaner & stopword removal Bahasa Indonesia
│       └── predict.py                      # Fungsi inferensi mandiri
├── resources/js/
│   ├── pages/
│   │   ├── Lapor/
│   │   │   ├── Create.vue                  # Form lapor warga cerdas & peta pinpoint
│   │   │   ├── Status.vue                  # Halaman lacak status publik (stepper)
│   │   │   └── Success.vue                 # Halaman sukses submit & unduh kode tiket
│   │   ├── Dashboard.vue                   # Board CRM Staf (List, Kanban, Heatmap View)
│   │   └── Admin/
│   │       ├── KategoriMapping.vue         # Panel Master Mapping Kategori -> Dinas (CRUD)
│   │       └── Statistik.vue               # Dashboard Analitik & Tombol Unduh CSV
│   └── components/
│       ├── LocationPicker.vue              # Peta interaktif penentu koordinat & reverse address
│       └── Dashboard/
│           ├── TicketCard.vue              # Kartu tiket dengan badge status & "⚠️ Perlu Review"
│           ├── TicketInspector.vue         # Panel detail tiket, komparasi AI, modal koreksi
│           └── HeatmapMap.vue              # Peta sebaran kepadatan aduan warga
├── database/
│   ├── migrations/                         # Seluruh skema database migrations
│   └── seeders/
│       └── DatabaseSeeder.php              # Seeder awal akun staf, dinas OPD, & mapping
├── tests/
│   ├── Feature/                            # 87 automated feature tests (Pest / PHPUnit)
│   │   ├── AduanPublicSubmissionTest.php
│   │   ├── DualLayerNlpClassificationTest.php
│   │   ├── AdminPanelAndCorrectionTest.php
│   │   ├── SecurityPentestAuditTest.php    # Suite uji penetrasi keamanan OWASP
│   │   └── LoadAndRateLimitTest.php        # Uji ketahanan beban & fail-safe fallback
│   └── Unit/
│       ├── GeocodeServiceTest.php
│       ├── RoutingLogicTest.php
│       └── PromptSanitizerTest.php
└── routes/
    ├── web.php                             # Rute web publik & halaman terautentikasi
    └── api.php                             # Rute API REST (classify, heatmap, geocode)
```

---

## ⚙️ Prasyarat Lingkungan (*Prerequisites*)

Pastikan mesin/server Anda telah terpasang:
- **PHP:** Versi `>= 8.2` (dengan ekstensi: `pdo`, `sqlite3`, `curl`, `mbstring`, `openssl`, `fileinfo`)
- **Composer:** Versi `>= 2.5`
- **Node.js:** Versi `>= 20.x` & **npm**
- **Python:** Versi `>= 3.10` & **pip**
- **Git**

---

## 🚀 Panduan Instalasi & Setup Lokal (*Local Setup Guide*)

### 1. Clone Repositori
```bash
git clone https://github.com/fakhri-rizaldi/sigap-bedas-ai.git
cd sigap-bedas-ai
```

### 2. Instalasi Dependensi PHP & Node.js
```bash
composer install
npm install
```

### 3. Konfigurasi File Environment (`.env`)
Salin file `.env.example` ke `.env`:
```bash
cp .env.example .env
php artisan key:generate
```
Buka file `.env` dan lengkapi konfigurasi utama:
```dotenv
APP_NAME="SIGAP BEDAS Lapor-AI"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Konfigurasi Gemini AI (Dapatkan di aistudio.google.com)
GEMINI_API_KEY=YOUR_GEMINI_API_KEY_HERE
GEMINI_MODEL=gemini-3.5-flash-lite
GEMINI_TIMEOUT=10

# Konfigurasi Model NLP Mandiri (FastAPI)
NLP_MICROSERVICE_URL=http://127.0.0.1:8001/predict
NLP_MICROSERVICE_TIMEOUT=3

# Konfigurasi Real-Time WebSocket Reverb
REVERB_APP_ID=533715
REVERB_APP_KEY=voyywzz0bsebqwbbcj3j
REVERB_APP_SECRET=4wlgqpmuac4it2qglyxj
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### 4. Setup Database & Storage Symlink
```bash
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
```
> **Akun Staf Default Hasil Seeder:**  
> - **Email:** `admin@bandungkab.go.id`  
> - **Password:** `password`

### 5. Setup Lingkungan Python & Latih Model NLP
```bash
pip install pandas scikit-learn joblib fastapi uvicorn
python nlp-model/train.py
```

---

## 🏃‍♂️ Menjalankan Sistem di Lingkungan Development

Untuk menjalankan ekosistem penuh di lingkungan lokal, buka **4 jendela terminal**:

| Terminal | Perintah | Fungsi | Alamat Akses |
|---|---|---|---|
| **Terminal 1** | `php artisan serve` | Web Server Laravel | `http://localhost:8000` |
| **Terminal 2** | `npm run dev` | Vite Dev Server (Hot-Reload) | `http://localhost:5173` |
| **Terminal 3** | `php artisan reverb:start` | Server WebSocket Real-Time | `ws://localhost:8080` |
| **Terminal 4** | `python nlp-model/app.py` | Microservice Model NLP Lokal | `http://127.0.0.1:8001` |

---

## 🧠 Arsitektur Machine Learning & Retraining Workflow (*Active Learning*)

Model NLP mandiri dirancang untuk terus berkembang seiring waktu dengan memanfaatkan data koreksi manual staf dinas (*Human-in-the-Loop Active Learning*).

### Siklus Retraining Model:
1. **Pengumpulan Data:** Staf dinas mengoreksi kategori aduan ambigu di dashboard. Setiap koreksi tersimpan di tabel `koreksi_kategoris`.
2. **Ekspor Dataset Baru:** Akses menu `/admin/statistik` dan klik tombol **"Unduh Dataset CSV (Retraining)"** (`/admin/statistik/export-csv`).
3. **Pembaruan Dataset:** Tempatkan CSV hasil ekspor ke dalam `nlp-model/data/` atau gabungkan dengan dataset lama.
4. **Jalankan Ulang Training:**
   ```bash
   python nlp-model/train.py
   ```
   Script akan melatih ulang model, mengevaluasi akurasi, dan otomatis menimpa file `models/svm_model.pkl` dan `models/tfidf.pkl`.
5. **Restart Microservice FastAPI:**
   Restart proses `python nlp-model/app.py` agar model baru termuat ke memori.

---

## 📡 Daftar Endpoint API & Routing Kunci

### Rute Publik Warga
- `GET /lapor` : Halaman form pelaporan publik dengan live AI & pinpoint map.
- `POST /lapor` : Endpoint submit aduan (dilindungi rate limit `throttle:15,1`).
- `GET /lapor/sukses/{kodeTiket}` : Konfirmasi sukses & kartu kode tiket.
- `GET /lapor/status/{kodeTiket}` : Pelacakan status aduan publik (stepper & catatan tindak lanjut).

### Rute Internal Staf & Admin (Dilindungi Auth)
- `GET /dashboard` : Workspace CRM Staf (Multi-view: List, Kanban Pipeline, Heatmap).
- `PATCH /dashboard/aduan/{id}/status` : Memperbarui status penanganan (`diproses`, `selesai`, `ditolak`) + menyiarkan event WebSocket ke publik.
- `PATCH /dashboard/aduan/{id}/koreksi` : Koreksi kategori manual + auto-reroute dinas + pencatatan log audit.
- `GET /admin/kategori-mapping` : Manajemen CRUD pemetaan kategori ke instansi dinas.
- `GET /admin/statistik` : Dasbor statistik analitik eksekutif.
- `GET /admin/statistik/export-csv` : Unduh dataset CSV aduan & koreksi untuk retraining.

### Rute API Backend
- `POST /api/aduan/classify` : Live AI auto-classification (Gemini + Local fallback).
- `GET /api/aduan/heatmap-data` : Data JSON sebaran aduan untuk Leaflet Heatmap.
- `GET /api/geocode` : Reverse geocoding koordinat ke alamat via OpenStreetMap.
- `GET /api/geocode/search` : Pencarian landmark & 31 kecamatan Kabupaten Bandung.

### Rute Microservice FastAPI (`http://127.0.0.1:8001`)
- `GET /health` : Health-check status server NLP lokal.
- `POST /predict` : Inferensi cepat model NLP (`{"text": "..."}` $\rightarrow$ `{"kategori": "...", "confidence": 0.95}`).

---

## 🧪 Pengujian Otomatis & Standar Keamanan (*Testing & QA*)

Proyek ini dilengkapi dengan **87 automated test cases** (365 assertions) yang mencakup feature test, unit test, dan uji penetrasi keamanan (*pentest*).

### Menjalankan Seluruh Pengujian Backend:
```bash
php artisan test
```

### Menjalankan Pengujian Python ML:
```bash
python nlp-model/test_nlp.py
```

### Matriks Keamanan yang Telah Di-Hardening:
- **Broken Access Control:** Seluruh halaman staf terkunci middleware `auth:verified`.
- **SQL Injection:** 100% menggunakan Eloquent ORM & PDO Parameterized Binding.
- **XSS Protection:** Input teks disanitasi dengan `strip_tags()` dan di-encode oleh Vue 3.
- **File Upload Security:** Ekstensi berkas dibatasi ketat (`jpg, jpeg, png, webp`) dengan limit 10MB.
- **Anti-Prompt Injection:** Filter kata kunci adversarial + Sandboxing XML terisolasi pada prompt LLM.
- **Anti-Spam / DoS:** Middleware rate limiting `throttle:15,1` pada endpoint submit warga.

---

## 🌐 Panduan Deployment Production & Server Hardening

### 1. Build Aset Frontend
```bash
npm run build
```

### 2. Optimasi Cache Laravel
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Konfigurasi Web Server Nginx (Reverse Proxy)
Contoh blok server Nginx:
```nginx
server {
    listen 80;
    server_name lapor.bandungkab.go.id;
    root /var/www/sigap-bedas-ai/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Proxy WebSocket Reverb
    location /app {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    }
}
```

### 4. Supervisor Configuration (Background Daemons)
Buat file `/etc/supervisor/conf.d/sigap-daemons.conf` untuk menjaga Reverb dan FastAPI tetap aktif:
```ini
[program:sigap-reverb]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/sigap-bedas-ai/artisan reverb:start
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/sigap-reverb.log

[program:sigap-fastapi]
directory=/var/www/sigap-bedas-ai/nlp-model
command=uvicorn app:app --host 127.0.0.1 --port 8001
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/sigap-fastapi.log
```

---

## 🔧 Troubleshooting & Solusi Kendala Umum

| Gejala Masalah | Penyebab | Solusi |
|---|---|---|
| **Port 8001 / 8080 Conflict** | Port sudah digunakan proses lain di background. | Matikan proses via Task Manager / Terminal (`kill -9 $(lsof -t -i:8001)`). |
| **cURL 30s Execution Timeout** | FastAPI lokal belum aktif saat backend mencoba memanggil port 8001. | Pastikan `python nlp-model/app.py` sudah berjalan. Sistem kini sudah dilengkapi `connectTimeout(1)` otomatis. |
| **Peta Tidak Muncul** | Leaflet container belum selesai di-render di DOM. | Komponen telah dipasang `nextTick()` dan `map.invalidateSize()`. Cek koneksi internet untuk memuat tile OpenStreetMap. |
| **WebSocket Reverb Disconnected** | Kunci `REVERB_APP_KEY` pada `.env` dan frontend Vite tidak sinkron. | Jalankan `npm run build` ulang setelah mengubah file `.env`. |
| **Akurasi AI Rendah pada Istilah Daerah** | Variasi kata belum terdaftar di kamus lokal. | Tambahkan sinonim ke `RuleBasedClassificationService.php` atau lakukan retraining model via `python nlp-model/train.py`. |

---

## 👥 Kontributor & Lisensi

- **Pengembang:** Fakhri Rizaldi & Tim Pengembang SIGAP Kabupaten Bandung
- **Institusi:** Program Kerja Praktik / Sistem Informasi Pemerintahan Kabupaten Bandung
- **Lisensi:** Open-Source under the [MIT License](LICENSE)
