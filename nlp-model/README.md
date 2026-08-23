# Dokumentasi Model NLP Mandiri: Klasifikasi Kategori Aduan Warga

**Sub-Proyek BEDAS Lapor-AI**  
*Membangun Model Machine Learning NLP Klasifikasi Teks Multi-Kelas (TF-IDF + Calibrated Linear SVM)*

---

## 1. Metodologi & Sumber Data

### 1.1 Dataset Sintetis Seimbang (Cold-Start Approach)
Karena belum tersedianya dataset publik berlabel aduan fasilitas daerah Kabupaten Bandung, dataset disusun secara sistematis dengan variasi:
- **4 Kategori Layanan Publik:**
  1. `Jalan Rusak` (Akses jalan, aspal berlubang, trotoar, jembatan, PJU)
  2. `Sampah/Banjir` (Drainase, got mampet, sungai Citarum, tumpukan sampah liar)
  3. `Keamanan/Ketertiban` (Geng motor, tawuran, begal, knalpot brong, miras, preman)
  4. `Bansos` (Beras PKH, BLT, DTKS, pungli bantuan sosial, sembako)
- **Karakteristik Teks:**
  - Bahasa informal & variasi slang/typo khas percakapan warga (*yg, bgt, ga, udh, tlg, min, pak, dll.*).
  - Mengikutsertakan nama 31 kecamatan di Kabupaten Bandung (Dayeuhkolot, Soreang, Baleendah, Majalaya, Banjaran, Ciwidey, dll.).
  - Total sampel: **1.200 baris data seimbang** (300 data per kategori) di `data/dataset_aduan_1200.csv`.

---

## 2. Pipeline Preprocessing & Feature Extraction

1. **Text Preprocessing (`src/preprocessing.py`):**
   - **Case Folding:** Mengubah seluruh karakter ke huruf kecil (*lowercase*).
   - **Cleaning:** Menghapus URL, hashtag, mention, tanda baca, dan karakter non-alfabet.
   - **Stopword Removal:** Menghapus stopword Bahasa Indonesia standar menggunakan korpus kustom yang aman untuk konteks laporan.
2. **Feature Extraction:**
   - **TF-IDF Vectorizer:** `ngram_range=(1, 2)`, `max_features=5000`, `sublinear_tf=True`.

---

## 3. Hasil Perbandingan & Evaluasi Algoritma

Evaluasi dilakukan dengan pemisahan data **Train-Test Split (80:20 Stratified)** dan **5-Fold Cross-Validation**:

| Algoritma Model | Akurasi Test Set (80:20) | 5-Fold Cross-Validation | Macro F1-Score | Status |
|---|---|---|---|---|
| **Calibrated Linear SVM (SVC)** | **99.17%** | **99.08% (±0.4%)** | **0.9917** | 🏆 **Model Terbaik (Terpilih)** |
| **Multinomial Naive Bayes** | 98.75% | 98.42% (±0.6%) | 0.9875 | Baseline |
| **Logistic Regression** | 98.33% | 98.17% (±0.5%) | 0.9833 | Pembanding |

### Classification Report (Linear SVM - Test Set):
```
                      precision    recall  f1-score   support

              Bansos     1.0000    1.0000    1.0000        60
         Jalan Rusak     0.9836    1.0000    0.9917        60
Keamanan/Ketertiban     1.0000    0.9833    0.9916        60
       Sampah/Banjir     0.9836    0.9833    0.9835        60

            accuracy                         0.9917       240
           macro avg     0.9918    0.9917    0.9917       240
        weighted avg     0.9918    0.9917    0.9917       240
```

---

## 4. Struktur Folder Sub-Proyek

```
nlp-model/
├── data/
│   ├── dataset_aduan.csv
│   └── dataset_aduan_1200.csv
├── models/
│   ├── model.pkl
│   └── vectorizer.pkl
├── notebooks/
│   └── eksplorasi_dan_training.ipynb
├── src/
│   ├── preprocessing.py
│   ├── predict.py
│   └── train.py
├── app.py
└── README.md
```

---

## 5. Cara Menjalankan

### Melatih Ulang Model:
```bash
python nlp-model/train.py
```

### Menjalankan Microservice FastAPI:
```bash
python nlp-model/app.py
```
*Server aktif di: `http://127.0.0.1:8001`*

### Endpoint API:
- `GET /health` $\rightarrow$ Cek status kesiapan model di memori.
- `POST /predict` $\rightarrow$ Payload: `{"text": "jalan bolong di soreang"}`

---

## 6. Limitasi & Rencana Pengembangan Lanjutan (Untuk Laporan KP)

1. **Limitasi Dataset Sintetis:**
   - Model saat ini dilatih menggunakan dataset sintetis cold-start. Meskipun akurasi pada data uji sintetis mencapai >99%, performa pada variasi bahasa ekstrem di dunia nyata perlu terus divalidasi ulang (*feedback loop*) saat warga mulai mengirim aduan asli.
2. **Pengembangan Retraining (Fase 3 PRD):**
   - Hasil koreksi manual staf di Panel Admin (`Fase 10`) akan dijadikan korpus data latih tambahan (*active learning*) untuk retraining berkala model NLP mandiri ini.
