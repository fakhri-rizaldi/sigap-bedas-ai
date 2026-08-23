# Style Guide: BEDAS Lapor-AI
### Panduan Desain Visual & UI — Konsisten dengan Identitas Website Resmi Pemerintah

| Field | Detail |
|---|---|
| Versi | 1.0 |
| Status | Draft |
| Tanggal | 20 Agustus 2026 |
| Cakupan | Web responsif (mobile-first) |

---

## 1. Prinsip Desain

Website resmi pemerintah Indonesia (contoh acuan: portal Kominfo, LAPOR!, Satu Data Indonesia, opendata.jabarprov.go.id) umumnya mengikuti prinsip berikut, yang menjadi dasar style guide ini:

1. **Formal & Terpercaya** — warna solid, tipografi jelas, minim dekorasi berlebihan. Menghindari kesan "startup playful" agar tetap kredibel sebagai layanan publik.
2. **Aksesibel** — kontras warna tinggi, ukuran teks terbaca, ramah untuk pengguna dari berbagai usia dan literasi digital.
3. **Fungsional di atas estetika** — informasi penting (status tiket, kategori, urgensi) harus langsung terlihat, bukan tersembunyi di balik animasi.
4. **Identitas kedaerahan** — menggunakan warna dan simbol yang mencerminkan identitas Kota/Kab. Bandung tanpa meniru logo resmi secara langsung.
5. **Responsif penuh** — mayoritas warga melapor dari HP, sehingga mobile adalah pengalaman utama (mobile-first), bukan desktop yang di-scale down.

---

## 2. Palet Warna

### 2.1 Warna Utama (Primary)
Mengikuti konvensi website pemerintah Indonesia yang umumnya memakai **biru tua/navy** sebagai warna institusional (kesan formal, birokrasi, kepercayaan) dan **merah** sebagai aksen (identitas nasional).

| Token | Hex | Penggunaan |
|---|---|---|
| `--color-primary` | `#0A3D62` | Header, navbar, tombol utama, elemen institusional |
| `--color-primary-dark` | `#062A45` | Hover state, footer |
| `--color-primary-light` | `#1E5A8A` | Background section sekunder, badge info |
| `--color-accent-red` | `#C0392B` | Aksen identitas, elemen penting non-urgensi (contoh: garis pemisah, ikon) |

### 2.2 Warna Status/Urgensi Aduan
Warna ini krusial karena dashboard dinas bergantung pada pembedaan visual cepat.

| Token | Hex | Makna |
|---|---|---|
| `--color-urgent` | `#D64545` | Urgensi **Tinggi/Darurat** |
| `--color-normal` | `#E9A400` | Urgensi **Normal/Menunggu** |
| `--color-resolved` | `#2E8B57` | Status **Selesai** |
| `--color-process` | `#3373B0` | Status **Diproses** |
| `--color-new` | `#6C757D` | Status **Baru/Belum Ditinjau** |

### 2.3 Warna Kategori Aduan
Digunakan sebagai badge/label agar dinas bisa memindai jenis aduan secara visual tanpa membaca teks.

| Kategori | Token | Hex |
|---|---|---|
| Jalan Rusak | `--color-cat-jalan` | `#8E5B3D` (coklat aspal) |
| Lingkungan & Drainase (Sampah/Banjir) | `--color-cat-lingkungan` | `#2E8B8B` (teal air) |
| Bansos | `--color-cat-bansos` | `#7A4FA3` (ungu kebijakan sosial) |
| Keamanan & Ketertiban | `--color-cat-keamanan` | `#B03A2E` (merah waspada) |

### 2.4 Warna Netral
| Token | Hex | Penggunaan |
|---|---|---|
| `--color-bg` | `#F4F6F8` | Background halaman |
| `--color-surface` | `#FFFFFF` | Card, form, panel |
| `--color-border` | `#D9DEE3` | Border input, divider |
| `--color-text-primary` | `#1B2733` | Teks utama |
| `--color-text-secondary` | `#5A6672` | Teks sekunder/caption |
| `--color-text-muted` | `#8A94A0` | Placeholder, teks non-aktif |

### 2.5 Kontras & Aksesibilitas
- Semua kombinasi teks-atas-background wajib memenuhi rasio kontras **minimum 4.5:1** (WCAG AA) untuk teks normal, dan **3:1** untuk teks besar (≥18px bold).
- Warna tidak boleh jadi satu-satunya penanda status — selalu sertakan label teks/ikon (contoh: badge urgensi harus ada tulisan "Tinggi", bukan hanya warna merah).

---

## 3. Tipografi

### 3.1 Font Family
Mengikuti konvensi situs pemerintah: sans-serif yang netral, terbaca jelas di layar kecil, dan tersedia gratis (menghindari lisensi font premium).

```css
--font-primary: 'Inter', 'Segoe UI', -apple-system, sans-serif;
--font-heading: 'Plus Jakarta Sans', 'Inter', sans-serif;
```

- **Inter** — untuk body text, form, tabel (netral, sangat terbaca di ukuran kecil).
- **Plus Jakarta Sans** — untuk heading/judul, memberi sedikit karakter modern-Indonesia tanpa terkesan kasual, cocok untuk identitas visual daerah.

### 3.2 Skala Ukuran (Mobile-First)

| Token | Mobile | Desktop | Penggunaan |
|---|---|---|---|
| `--text-xs` | 12px | 12px | Caption, metadata, timestamp |
| `--text-sm` | 14px | 14px | Label form, teks sekunder |
| `--text-base` | 16px | 16px | Body text, isi aduan |
| `--text-lg` | 18px | 20px | Subjudul card |
| `--text-xl` | 22px | 26px | Judul section |
| `--text-2xl` | 26px | 34px | Judul halaman/hero |

- Ukuran dasar body **minimum 16px** — standar aksesibilitas pemerintah, mencegah pengguna harus zoom di mobile.
- Line-height body: `1.6` untuk keterbacaan teks aduan yang panjang.

### 3.3 Bobot Font
| Token | Weight | Penggunaan |
|---|---|---|
| `--font-regular` | 400 | Body text |
| `--font-medium` | 500 | Label, tombol sekunder |
| `--font-semibold` | 600 | Judul card, tombol utama |
| `--font-bold` | 700 | Judul halaman |

---

## 4. Layout & Grid

### 4.1 Breakpoints (Mobile-First)

```css
--bp-sm: 375px;   /* HP kecil */
--bp-md: 768px;   /* Tablet */
--bp-lg: 1024px;  /* Desktop kecil / dashboard dinas */
--bp-xl: 1280px;  /* Desktop besar */
```

Prinsip: desain default untuk 375px lebar (HP standar warga), lalu diperluas ke breakpoint lebih besar — bukan sebaliknya.

### 4.2 Grid & Spacing

```css
--space-1: 4px;
--space-2: 8px;
--space-3: 12px;
--space-4: 16px;
--space-5: 24px;
--space-6: 32px;
--space-8: 48px;
```

- Container max-width: `1200px` (dashboard dinas), `680px` (form pelaporan publik — dibuat lebih sempit agar fokus, tidak melebar canggung di desktop).
- Padding horizontal halaman: `16px` di mobile, `32px` di desktop.

### 4.3 Struktur Halaman Publik (Form Pelaporan)
```
┌─────────────────────────────┐
│ Header (logo + nama layanan) │
├─────────────────────────────┤
│ Form aduan (single column)   │
│  - Textarea keluhan          │
│  - Badge kategori (live)     │
│  - Upload foto               │
│  - Peta pin lokasi           │
│  - Tombol submit             │
├─────────────────────────────┤
│ Footer (kontak, disclaimer)  │
└─────────────────────────────┘
```
Single-column di semua breakpoint untuk form publik — menghindari kebingungan alur pengisian, konsisten dari mobile ke desktop.

### 4.4 Struktur Dashboard Dinas
- **Mobile**: stack vertikal — filter di atas (collapsible), lalu list tiket, peta bisa diakses via tab terpisah.
- **Desktop (≥1024px)**: layout 2 kolom — sidebar filter + list tiket (kiri, 40%), peta heatmap (kanan, 60%), dapat di-toggle.

---

## 5. Komponen UI

### 5.1 Tombol (Button)

| Jenis | Style |
|---|---|
| Primary | Background `--color-primary`, teks putih, radius 6px, padding 12px 24px |
| Secondary | Border `--color-primary`, teks `--color-primary`, background transparan |
| Danger/Urgent | Background `--color-urgent`, untuk aksi terkait aduan darurat |
| Disabled | Opacity 50%, cursor not-allowed |

- Tinggi minimum tombol: **44px** (standar target sentuh mobile/touch-friendly, sesuai pedoman aksesibilitas).
- Radius sudut: `6px` — cukup lembut tapi tetap formal (bukan pill/rounded penuh yang terkesan konsumer).

### 5.2 Form Input
- Border `1px solid var(--color-border)`, radius `6px`, padding `12px 16px`.
- Focus state: border `--color-primary` + subtle shadow (`0 0 0 3px rgba(10,61,98,0.15)`).
- Textarea aduan: minimum tinggi 120px, auto-resize mengikuti panjang teks.
- Badge kategori live muncul di bawah textarea saat mengetik (bukan menimpa area input), dengan transisi fade-in halus, bukan muncul tiba-tiba.

### 5.3 Badge Kategori & Urgensi
```
[●] Lingkungan & Drainase — 94%     ← badge kategori, warna sesuai token kategori
[⚠] Urgensi: Tinggi                  ← badge urgensi, warna --color-urgent
```
- Bentuk pill kecil, padding `4px 12px`, font-size `--text-sm`, font-weight `--font-medium`.
- Ikon di depan label untuk mempercepat pemindaian visual oleh staf dinas.

### 5.4 Card Tiket Aduan (Dashboard)
```
┌───────────────────────────────┐
│ [Badge Kategori]  [Badge Urgensi] │
│ "Jalan tergenang sampah di..."  │
│ 📍 Dayeuhkolot, Kab. Bandung     │
│ 🕐 2 jam lalu        [Status: Baru] │
└───────────────────────────────┘
```
- Background `--color-surface`, border `1px solid --color-border`, radius `8px`, shadow tipis (`0 1px 3px rgba(0,0,0,0.08)`).
- Hover (desktop): elevasi shadow sedikit naik, border berubah ke `--color-primary-light`.

### 5.5 Peta & Heatmap
- Marker warna mengikuti token urgensi (merah = tinggi, kuning = normal).
- Kontrol zoom & layer toggle diposisikan di pojok kanan bawah (standar UX peta, tidak menghalangi konten).
- Di mobile, peta default tinggi `50vh` dengan tombol "perbesar peta" untuk fullscreen.

### 5.6 Navigasi (Header)
- Logo instansi + nama layanan "BEDAS Lapor-AI" rata kiri.
- Menu utama rata kanan (desktop) → hamburger menu (mobile, breakpoint < 768px).
- Warna header: `--color-primary`, teks putih, tinggi tetap `64px`.

---

## 6. Ikonografi

- Gunakan icon set **outline/line-style** yang konsisten (contoh: Lucide Icons, Heroicons) — bukan campur beberapa gaya berbeda.
- Hindari ikon flat-3D atau ilustrasi kartun berlebihan yang mengurangi kesan formal instansi.
- Ukuran ikon standar: `20px` inline dengan teks, `24px` untuk ikon aksi tombol.

---

## 7. Responsivitas — Ringkasan Perilaku per Breakpoint

| Elemen | Mobile (<768px) | Tablet (768–1023px) | Desktop (≥1024px) |
|---|---|---|---|
| Navigasi | Hamburger menu | Hamburger menu | Menu horizontal penuh |
| Form pelaporan | Single column, full-width | Single column, max-width 600px centered | Single column, max-width 680px centered |
| Dashboard tiket + peta | Stack (tab switch) | Stack, peta lebih tinggi | Side-by-side 2 kolom |
| Card tiket | 1 kolom | 2 kolom grid | 3 kolom grid (list view) atau tetap 1 kolom (jika ada peta di samping) |
| Font judul halaman | 26px | 30px | 34px |

---

## 8. Nada Bahasa & Microcopy

Konsisten dengan gaya layanan publik: **sopan, jelas, tidak birokratis-kaku, tidak juga terlalu santai**.

- ✅ "Laporan Anda telah kami terima dan diteruskan ke Dinas Lingkungan Hidup."
- ❌ "Yeay! Laporanmu berhasil terkirim 🎉" (terlalu kasual untuk layanan resmi)
- ❌ "Permohonan Saudara telah diregistrasi dalam sistem." (terlalu kaku/birokratis)

Pesan error dan validasi form harus jelas dan actionable, contoh:
- ✅ "Mohon unggah foto agar lokasi dapat terdeteksi otomatis, atau tandai lokasi secara manual di peta."

---

## 9. Contoh Implementasi CSS Variables (Root)

```css
:root {
  /* Warna Utama */
  --color-primary: #0A3D62;
  --color-primary-dark: #062A45;
  --color-primary-light: #1E5A8A;
  --color-accent-red: #C0392B;

  /* Status Urgensi */
  --color-urgent: #D64545;
  --color-normal: #E9A400;
  --color-resolved: #2E8B57;
  --color-process: #3373B0;
  --color-new: #6C757D;

  /* Netral */
  --color-bg: #F4F6F8;
  --color-surface: #FFFFFF;
  --color-border: #D9DEE3;
  --color-text-primary: #1B2733;
  --color-text-secondary: #5A6672;
  --color-text-muted: #8A94A0;

  /* Tipografi */
  --font-primary: 'Inter', 'Segoe UI', -apple-system, sans-serif;
  --font-heading: 'Plus Jakarta Sans', 'Inter', sans-serif;

  /* Spacing */
  --space-1: 4px;
  --space-2: 8px;
  --space-3: 12px;
  --space-4: 16px;
  --space-5: 24px;
  --space-6: 32px;
  --space-8: 48px;

  /* Radius */
  --radius-sm: 4px;
  --radius-md: 6px;
  --radius-lg: 8px;
}
```

---

## 10. Catatan Implementasi (Laravel/Livewire)

- Simpan variabel warna & spacing ini di `resources/css/app.css` sebagai CSS custom properties agar bisa dipakai konsisten baik di Blade view maupun komponen Livewire.
- Jika memakai Tailwind (umum untuk starter kit Laravel), extend `tailwind.config.js` dengan token warna di atas agar class seperti `bg-primary`, `text-urgent` tersedia langsung.
- Komponen badge kategori/urgensi sebaiknya dibuat sebagai Blade component (`<x-badge-urgensi status="tinggi" />`) agar konsisten dipakai ulang di form, dashboard, dan detail tiket.

---

*Style guide ini adalah acuan awal dan dapat disesuaikan setelah proses desain mockup/prototipe dengan stakeholder dinas.*
