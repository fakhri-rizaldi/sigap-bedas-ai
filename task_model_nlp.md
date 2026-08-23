# Task Breakdown: Model NLP Klasifikasi Kategori Aduan Warga
### Sub-Proyek dari BEDAS Lapor-AI

Referensi: `prd_model_nlp.md`, `dataset_aduan.csv`

---

## Fase 0 — Setup Environment

- [x] Install Python environment
- [x] Siapkan pustaka: `pandas`, `scikit-learn`, `joblib`, `fastapi`, `uvicorn`
- [x] Setup script preprocessing & training
- [x] Siapkan struktur folder:
  ```
  nlp-model/
  ├── data/
  │   ├── dataset_aduan.csv
  │   └── dataset_aduan_1200.csv
  ├── notebooks/
  │   └── eksplorasi_dan_training.ipynb
  ├── models/
  │   ├── model.pkl
  │   └── vectorizer.pkl
  ├── src/
  │   ├── preprocessing.py
  │   ├── train.py
  │   └── predict.py
  ├── app.py
  └── README.md
  ```

## Fase 1 — Dataset

- [x] Buat dataset sintetis awal (`dataset_aduan.csv`)
- [x] Review manual dataset & seimbangkan dataset menjadi 1.200 baris (`dataset_aduan_1200.csv`)
- [x] Hitung distribusi jumlah sampel per kategori (300 data seimbang per kategori)
- [x] Split dataset: 80:20 train-test split stratified

## Fase 2 — Exploratory Data Analysis (EDA)

- [x] Analisis distribusi kelas (seimbang 300 per kelas)
- [x] Dokumentasikan di `nlp-model/notebooks/eksplorasi_dan_training.ipynb`

## Fase 3 — Preprocessing Teks

- [x] Buat fungsi `clean_text(text)` (lowercasing, tanda baca, url, hashtag removal)
- [x] Buat fungsi `remove_stopwords(text)` untuk Bahasa Indonesia
- [x] Simpan pipeline preprocessing di `nlp-model/src/preprocessing.py`

## Fase 4 — Feature Extraction (TF-IDF)

- [x] Inisialisasi `TfidfVectorizer(ngram_range=(1,2), max_features=5000, sublinear_tf=True)`
- [x] Fit vectorizer pada data training & transform data testing

## Fase 5 — Training Model (Bandingkan Beberapa Algoritma)

- [x] Latih **Multinomial Naive Bayes** (Baseline)
- [x] Latih **Calibrated Linear SVM** (Model Terbaik)
- [x] Latih **Logistic Regression** (Pembanding)

## Fase 6 — Evaluasi Model

- [x] Hitung `accuracy_score` untuk setiap model
- [x] Generate `classification_report` (Precision, Recall, F1-Score)
- [x] Dokumentasikan perbandingan performa di `nlp-model/README.md`

## Fase 7 — Cross-Validation (Validasi Tambahan)

- [x] Terapkan 5-fold `cross_val_score` (Linear SVM: 99.08% ± 0.4%)
- [x] Catat rata-rata & standar deviasi skor cross-validation

## Fase 8 — Error Analysis

- [x] Uji sanity check kalimat baru & analisis kata kunci ambigu

## Fase 9 — Finalisasi & Ekspor Model

- [x] Latih ulang model terbaik pada seluruh 1.200 sampel dataset
- [x] Simpan model final: `nlp-model/models/model.pkl`
- [x] Simpan vectorizer: `nlp-model/models/vectorizer.pkl`
- [x] Buat script inferensi mandiri di `nlp-model/src/predict.py`
- [x] Test fungsi `predict()` dengan contoh kalimat baru

## Fase 10 — Dokumentasi (untuk Laporan KP)

- [x] Tulis metodologi lengkap di `nlp-model/README.md`
- [x] Sertakan tabel hasil evaluasi perbandingan model
- [x] Tulis bagian limitasi dataset sintetis & rencana active learning retraining

## Fase 11 — Integrasi ke Sistem Utama BEDAS Lapor-AI

- [x] Deploy model sebagai microservice FastAPI (`nlp-model/app.py` - `POST /predict`)
- [x] Integrasikan sebagai lapisan validasi silang (Dual-Layer AI) terhadap Gemini API di Laravel
- [x] Tandai `perlu_review = true` pada tiket yang memiliki diskrepansi kategori antar kedua model

---

*Dokumen ini adalah breakdown teknis dari `prd_model_nlp.md`. Seluruh fase telah selesai diimplementasikan dan tervalidasi.*
