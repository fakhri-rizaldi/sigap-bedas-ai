# 🏛️ BEDAS Lapor-AI (Sistem Pelaporan Aduan Warga Cerdas)

> **Platform CRM & Layanan Aspirasi/Pengaduan Warga Kabupaten Bandung Berbasis Kecerdasan Buatan (Dual-Layer AI: Google Gemini + Model NLP Mandiri).**

---

## 🚀 Fitur Utama
1. **Form Publik Cerdas (`/lapor`):**
   - Live AI auto-classification (Kategori, Urgensi, Rekomendasi Dinas).
   - Auto-kompresi foto canvas cerdas (bisa upload foto berukuran besar hingga >10MB).
   - Leaflet Pinpoint Map + Reverse Geocoding alamat otomatis.
2. **Pelacakan Status Aduan (`/lapor/status/{kodeTiket}`):**
   - Visual stepper 3-tahap (Diterima $\rightarrow$ Diproses $\rightarrow$ Selesai).
   - Catatan tindak lanjut resmi dari OPD terkait.
3. **CRM Staff & Dinas Dashboard (`/dashboard`):**
   - Tampilan multi-mode: **List View**, **Kanban Pipeline**, dan **Peta Sebaran / Heatmap Kepadatan Aduan**.
   - Single-row filter terintegrasi (Status, Urgensi, Instansi Dinas, 31 Kecamatan).
   - Real-time live sync via Laravel Reverb WebSocket.
4. **Klasifikasi Dua Lapis (*Dual-Layer AI*):**
   - Validasi silang antara **Gemini 3.5 Flash-Lite** dan **Model NLP Mandiri (Python TF-IDF + Linear SVM via FastAPI)**.
   - Deteksi kasus ambigu otomatis dengan flag **`⚠️ Perlu Review`**.

---

## 💻 Panduan Setup untuk Teman Sekelompok

### 1. Clone & Masuk ke Folder Proyek
```bash
git clone <URL_REPO_GITHUB>
cd bedas-lapor-ai
```

### 2. Install Dependency (PHP & Node.js)
```bash
composer install
npm install
```

### 3. Konfigurasi File Environment
```bash
cp .env.example .env
php artisan key:generate
```
*Buka `.env` dan masukkan `GEMINI_API_KEY` milik Anda.*

### 4. Database & Storage Link
```bash
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
```

### 5. Setup Model NLP (Python)
```bash
pip install pandas scikit-learn joblib fastapi uvicorn
python nlp-model/train.py
```

---

## 🏃‍♂️ Menjalankan Aplikasi Lokal (Multi-Terminal)

Buka **4 Terminal** untuk menjalankan seluruh ekosistem:

### Terminal 1: Laravel Web Server
```bash
php artisan serve
```
*Akses di: `http://localhost:8000`*

### Terminal 2: Vite Dev Server (Frontend Hot-Reload)
```bash
npm run dev
```

### Terminal 3: Laravel Reverb (WebSocket Real-Time)
```bash
php artisan reverb:start
```

### Terminal 4: Microservice NLP Lokal (FastAPI)
```bash
python nlp-model/app.py
```
*Server aktif di: `http://127.0.0.1:8001`*

---

## 🧪 Menjalankan Pengujian Otomatis
```bash
php artisan test
```
*Seluruh 64 test cases feature dan unit test akan berjalan otomatis.*
