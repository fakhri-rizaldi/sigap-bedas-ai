# PRD: Model NLP Klasifikasi Kategori Aduan Warga
### Sub-Proyek dari BEDAS Lapor-AI — Membangun Model Klasifikasi Teks Mandiri

| Field | Detail |
|---|---|
| Versi | 1.0 |
| Status | Draft — untuk diusulkan ke pembimbing kerja praktik |
| Tanggal | 22 Agustus 2026 |
| Bagian dari | BEDAS Lapor-AI (Ide 1) |

---

## 1. Latar Belakang

Sub-proyek ini fokus khusus pada pembangunan **model machine learning NLP yang dilatih sendiri** (bukan sekadar memanggil API pihak ketiga) untuk mengklasifikasikan teks aduan warga ke dalam 4 kategori:

1. **Jalan Rusak**
2. **Sampah/Banjir** (Lingkungan & Drainase)
3. **Bansos**
4. **Keamanan/Ketertiban**

Karena tidak tersedia dataset publik siap pakai yang sesuai dengan konteks aduan fasilitas lokal (lihat riset yang sudah dilakukan sebelumnya — dataset LAPOR! tidak dipublikasikan, Open Data Bandung/Jabar belum berlabel kategori), sub-proyek ini akan membangun **dataset sintetis secara manual** sebagai fondasi awal pelatihan model.

## 2. Tujuan

- Membangun dataset teks aduan berlabel kategori (buatan sendiri/sintetis) sebagai bahan pelatihan.
- Melatih model klasifikasi teks NLP (TF-IDF + algoritma klasik seperti SVM/Naive Bayes/Logistic Regression) yang mampu mengklasifikasikan teks aduan ke 4 kategori secara otomatis.
- Mengevaluasi akurasi model dengan metrik standar (accuracy, precision, recall, F1-score per kelas).
- Menyediakan model yang dapat diintegrasikan ke sistem BEDAS Lapor-AI sebagai alternatif/pelengkap klasifikasi via Gemini API — baik sebagai model utama (offline, tanpa biaya API) maupun sebagai lapisan validasi silang.

## 3. Mengapa Dataset Sintetis (Manual)

- Tidak ada dataset publik yang cocok dengan domain spesifik ini (fasilitas lingkungan lokal Indonesia).
- Dataset sintetis dibuat dengan gaya bahasa informal/typo/singkatan khas warga saat melapor (mirip pesan WhatsApp atau media sosial), agar model terbiasa dengan variasi bahasa nyata, bukan bahasa formal buku teks.
- Pendekatan ini adalah metode yang lazim digunakan sebagai *cold-start* dataset ketika data riil belum tersedia, dan akan digantikan/diperkaya dengan data asli begitu sistem BEDAS Lapor-AI berjalan dan mengumpulkan aduan nyata dari warga (feedback loop, sesuai roadmap Fase 3 di `prd.md` utama).
- Perlu didokumentasikan secara jujur di laporan KP sebagai keterbatasan (limitation) — model dilatih dari data sintetis, sehingga performa di dunia nyata perlu divalidasi ulang begitu data asli tersedia.

## 4. Lingkup (Scope)

### 4.1 Termasuk (In-Scope)
- Pembuatan dataset sintetis berlabel (teks aduan + kategori), minimal 40-50 sampel per kategori (160-200 total) untuk MVP model.
- Preprocessing teks (case folding, stopword removal Bahasa Indonesia, stemming/lemmatization opsional menggunakan Sastrawi).
- Feature extraction menggunakan TF-IDF.
- Pelatihan & perbandingan beberapa algoritma klasik: Naive Bayes, SVM, Logistic Regression.
- Evaluasi model: train-test split, confusion matrix, classification report (precision/recall/F1 per kelas).
- Ekspor model terlatih (`.pkl`/`joblib`) untuk siap diintegrasikan ke backend.
- Dokumentasi metodologi lengkap (untuk laporan KP): sumber data, cara pembuatan dataset, preprocessing, arsitektur model, hasil evaluasi.

### 4.2 Tidak Termasuk (Out-of-Scope)
- Deep learning / model berbasis transformer (BERT/IndoBERT) — dicatat sebagai potensi pengembangan lanjutan, bukan wajib di tahap ini karena kompleksitas komputasi lebih tinggi.
- Deteksi urgensi (High/Normal) — fokus sub-proyek ini murni klasifikasi kategori, urgensi tetap ditangani terpisah (rule-based/Gemini API sesuai `prd.md` utama).
- Integrasi penuh ke aplikasi Laravel — sub-proyek ini fokus pada model, integrasi ke sistem web dicatat sebagai langkah lanjutan setelah model siap.
- Active learning / retraining otomatis — dicatat sebagai roadmap Fase 3 (setelah data asli terkumpul dari sistem berjalan).

## 5. Kebutuhan Fungsional (Level Model/Data Science)

| ID | Kebutuhan | Prioritas |
|---|---|---|
| M1 | Dataset sintetis berlabel 4 kategori, format CSV (`teks`, `kategori`) | Must |
| M2 | Pipeline preprocessing teks (cleaning, stopword removal, tokenisasi) | Must |
| M3 | Feature extraction TF-IDF | Must |
| M4 | Pelatihan minimal 2 algoritma pembanding (misal Naive Bayes vs SVM) | Must |
| M5 | Evaluasi model dengan train-test split (80:20 atau cross-validation) | Must |
| M6 | Laporan hasil evaluasi (confusion matrix, classification report) | Must |
| M7 | Ekspor model & vectorizer terlatih untuk digunakan di inference | Must |
| M8 | Script/fungsi prediksi standalone (input teks → output kategori + confidence) | Must |
| M9 | Dokumentasi metodologi untuk laporan KP | Must |
| M10 | Notebook eksploratif (EDA dataset: distribusi kelas, panjang teks, kata paling sering muncul per kategori) | Should |
| M11 | Augmentasi data tambahan jika ditemukan kelas timpang (class imbalance) | Should |

## 6. Metodologi

1. **Pembuatan Dataset** — disusun manual (dengan bantuan brainstorming variasi kalimat), mencakup variasi:
   - Gaya bahasa formal & informal.
   - Typo dan singkatan umum (yg, ga, bgt, dr, dll).
   - Penyebutan lokasi generik khas Kab. Bandung (Dayeuhkolot, Baleendah, Soreang, Margahayu, Banjaran, Ciwidey, dll) agar konteks realistis.
   - Variasi panjang kalimat (pendek 1 kalimat, hingga 2-3 kalimat).

2. **Preprocessing**:
   - Lowercasing, penghapusan tanda baca & angka tidak relevan.
   - Stopword removal Bahasa Indonesia (library Sastrawi atau daftar stopword custom).
   - Tokenisasi.
   - (Opsional) Stemming menggunakan Sastrawi.

3. **Feature Extraction**: TF-IDF Vectorizer (scikit-learn), dengan eksperimen n-gram range (unigram vs unigram+bigram).

4. **Modeling**: bandingkan minimal 2 algoritma:
   - Multinomial Naive Bayes (baseline umum untuk klasifikasi teks).
   - Support Vector Machine (SVM) dengan kernel linear.
   - (Opsional) Logistic Regression sebagai pembanding ketiga.

5. **Evaluasi**:
   - Split data latih/uji (80:20), atau k-fold cross-validation jika dataset kecil.
   - Metrik: accuracy, precision, recall, F1-score per kelas + macro average.
   - Confusion matrix untuk melihat kelas mana yang paling sering tertukar.

6. **Ekspor & Integrasi**:
   - Simpan model (`model.pkl`) dan vectorizer (`vectorizer.pkl`) menggunakan `joblib`.
   - Buat fungsi/script `predict(text) → {kategori, confidence}` yang siap dipanggil, baik sebagai microservice Python terpisah (FastAPI) maupun dipanggil dari Laravel via HTTP internal.

## 7. Target Performa

- **Target awal (MVP dengan dataset sintetis)**: akurasi ≥ 80% pada data uji (test set dari dataset sintetis yang sama).
- **Catatan penting**: akurasi tinggi pada dataset sintetis **tidak otomatis mencerminkan performa di dunia nyata** — perlu validasi ulang begitu tersedia data aduan asli dari sistem BEDAS Lapor-AI yang sudah berjalan. Ini harus disebutkan eksplisit sebagai limitasi di laporan KP.

## 8. Rencana Integrasi ke Sistem Utama

Setelah model siap dan tervalidasi:
- **Opsi A**: deploy sebagai microservice Python (FastAPI) terpisah, dipanggil dari Laravel via HTTP internal — sesuai opsi yang sudah dibahas di awal diskusi stack teknis.
- **Opsi B**: gunakan sebagai lapisan validasi silang terhadap hasil Gemini API (jika kedua model sepakat → confidence tinggi; jika berbeda → tandai untuk review manual staf dinas).
- Keputusan final Opsi A/B didiskusikan setelah model MVP selesai dan hasil evaluasinya diketahui.

## 9. Deliverables

1. `dataset_aduan.csv` — dataset sintetis berlabel.
2. Notebook/script training model (`train_model.py` atau `.ipynb`).
3. Model terlatih (`model.pkl`, `vectorizer.pkl`).
4. Laporan evaluasi (classification report, confusion matrix — bisa dalam bentuk gambar/markdown).
5. Dokumentasi metodologi (untuk bab metodologi laporan KP).

## 10. Risiko & Mitigasi

| Risiko | Mitigasi |
|---|---|
| Dataset sintetis tidak merepresentasikan bahasa aduan asli warga | Buat variasi bahasa seluas mungkin (formal-informal, typo, singkatan); validasi ulang dengan data asli begitu tersedia |
| Class imbalance (satu kategori jauh lebih banyak/sedikit contohnya) | Pastikan jumlah sampel per kategori seimbang saat pembuatan dataset; augmentasi jika perlu |
| Overfitting pada dataset kecil | Gunakan cross-validation, hindari model terlalu kompleks untuk ukuran data yang tersedia |
| Model bagus di data sintetis tapi jelek di data nyata | Dicatat sebagai limitasi eksplisit; rencanakan retraining dengan data asli sebagai bagian dari roadmap Fase 3 sistem utama |

## 11. Keterkaitan dengan Dokumen Lain

- Sub-proyek ini melengkapi `prd.md` (BEDAS Lapor-AI utama) bagian 9 (Detail Integrasi Gemini API) — model NLP mandiri ini menjadi **alternatif/pelengkap**, bukan pengganti total, dari pendekatan Gemini API zero-shot yang sudah direncanakan.
- Breakdown teknis tersedia di `task_model_nlp.md`.
- Dataset awal tersedia di `dataset_aduan.csv`.

---

*Dokumen ini disiapkan sebagai bahan usulan kerja praktik, melengkapi PRD BEDAS Lapor-AI (Ide 1) dan BEDAS-SentimenPublik (Ide 2).*
