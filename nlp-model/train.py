import os
import sys
import random
import re
import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split, cross_val_score
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.naive_bayes import MultinomialNB
from sklearn.svm import LinearSVC
from sklearn.calibration import CalibratedClassifierCV
from sklearn.linear_model import LogisticRegression
from sklearn.metrics import classification_report, accuracy_score, confusion_matrix
import joblib

# Import preprocessing
sys.path.append(os.path.dirname(os.path.abspath(__file__)))
from src.preprocessing import preprocess

def ensure_dataset():
    """Memastikan dataset 1200 sampel tersedia."""
    csv_path = 'dataset_aduan_1200.csv'
    if os.path.exists(csv_path):
        print(f"[*] Memuat dataset dari {csv_path}...")
        return pd.read_csv(csv_path)
    
    print("[*] Membuat dataset_aduan_1200.csv dari generator...")
    try:
        df_orig = pd.read_csv('dataset_aduan.csv')
    except Exception:
        df_orig = pd.DataFrame(columns=['teks', 'kategori'])

    locations = [
        "Dayeuhkolot", "Baleendah", "Banjaran", "Ciwidey", "Margahayu", "Soreang", "Katapang", "Ciparay",
        "Pameungpeuk", "Cileunyi", "Rancaekek", "Cicalengka", "Majalaya", "Pangalengan", "Cimenyan",
        "Cilengkrang", "Solokan Jeruk", "Paseh", "Ibun", "Nagreg", "Kutawaringin", "Cangkuang", "Arjasari",
        "Pasirjambu", "Bojongsoang", "Manggahang", "Sukamenak", "Cikoneng", "Bojongmalaka", "Ciganitri",
        "Rancamanyar", "Kopo", "Cibaduyut", "Tegalluar", "Sapan", "Gedebage", "Cibiru", "Ujungberung"
    ]

    cat_components = {
        'Jalan Rusak': {
            'subjects': ["Jalan raya", "Jln protokol", "Aspal jalan", "Akses jalan masuk", "Jalan utama", "Jalan alternatif", "Jalan gang", "Jalan desa", "Jalur tanjakan", "Jalan dekat pasar", "Jembatan kecil", "Jalan komplek", "Trotoar", "Jalan poros", "Tikungan jalan", "Paving block jalan"],
            'conditions': ["berlubang parah", "ancur lebur", "rusak berat gak dibenerin", "banyak jeglongan sewu", "amblas separuh badan", "retak-retak parah", "aspalnya udah ngelupas abis", "penuh kubangan air", "bergelombang bikin oleng", "licin dan banyak kerikil tajam", "rusak parah abis dilewatin truk", "bolong dalem banget", "aspal hotmix nya ngelotok", "rusak kena kikis air comberan"],
            'impacts': ["bikin motor sering jatoh tiap malem", "bikin velg motor peang dan ban bocor", "bikin macet parah tiap pagi", "bahaya bgt buat anak2 sekolah", "mobil ceper sering mentok", "pemotor sering kaget ngerem mendadak", "udh brp kali ada korban kecelakaan", "warga was2 tiap lewat apalagi pas ujan", "bikin debu tebel kalo panas", "angkot ga mau lewat situ lg", "shockbreaker motor jebol"],
            'pleas': ["tolong dinas pupr segera aspal ulang", "mohon pak bupati gercep benerin", "tolong pak lurah perhatikan jalan kami", "min tolong sampaikan ke dinas terkait", "kapan mau ditambal min?", "tolong secepatnya di perbaiki min", "jangan nunggu ada korban jiwa baru dibenerin", "pliss segera di tindaklanjuti pa", "minta tolong segera di hotmix"]
        },
        'Sampah/Banjir': {
            'subjects': ["Sampah di TPS", "Tumpukan sampah liar", "Sungai/kali", "Got depan rumah warga", "Drainase pemukiman", "Saluran air induk", "Tempat penampungan sampah", "Banjir tahunan", "Air selokan", "Gorong-gorong", "Kali dekat pasar", "Genangan cileuncang", "Sampah rumah tangga", "Pintu air"],
            'conditions': ["mampet total gara-gara plastik dan sampah", "meluap ke jalan tiap ujan lebat", "menggunung udah berminggu-minggu ga diangkut", "baunya nusuk hidung bikin enek", "penuh belatung dan lalat hijau", "merendam rumah warga setinggi paha", "ga surut-surut udah 3 hari", "bikin bau comberan masuk ke kamar", "penuh sampah kasur dan plastik", "airnya item pekat dan berbau busuk", "tanggulnya rembes dan bocor", "lumpur sedimennya udah tebel bgt"],
            'impacts': ["bikin warga gatal-gatal dan batuk", "akses jalan lumpuh total ga bisa lewat motor", "barang elektronik pada rusak kerendem", "banyak nyamuk DBD", "warga terpaksa ngungsi ke mushola", "anak sekolah ga bisa lewat", "bikin lingkungan kumuh dan bau menyengat", "motor mogok massal pas nekat nerobos", "bau busuk kecium sampe radius 300 meter"],
            'pleas': ["tolong armada truk sampah segera dateng", "mohon dinas lingkungan hidup turun tangan", "tolong keruk selokan min", "minta bantuan pompa air sedot banjir min", "tolong buatin tanggul darurat pa kades", "butuh perahu karet buat evakuasi warga", "dinas kebersihan tolong angkut sampahnya", "tolong pak camat segera tinjau lokasi"]
        },
        'Keamanan/Ketertiban': {
            'subjects': ["Geng motor ugal-ugalan", "Aksi curanmor", "Aksi tawuran remaja", "Preman pasar", "Balap liar tengah malam", "Pemuda mabuk-mabukan", "Aksi begal bersenjata", "Maling jemuran dan gas lpg", "Pungutan liar oknum", "Judi online slot", "Pelecehan / catcalling", "Sound horeg hajatan", "Pencurian rumah kosong", "Warung miras ilegal", "Knalpot brong bising"],
            'conditions': ["makin marak dan meresahkan warga", "berkeliaran bawa senjata tajam / cerulit", "bikin suara bising ga bisa tidur tiap malam", "terjadi terus tiap malam minggu", "maksa minta uang keamanan ke pedagang kecil", "nongkrong di pos ronda sambil nenggak ciu", "beraksi pas subuh warga lagi sholat", "sering lempar batu dan botol kaca", "neror anak-anak sekolah pas pulang", "nyolong barang warga secara beruntun"],
            'impacts': ["warga takut keluar rumah malem hari", "korban mengalami luka sabetan sajam", "banyak kehilangan motor dlm sepekan", "anak balita kaget nangis kebangun", "pedagang kecil rugi diperas terus", "lingkungan jadi rawan kriminal", "warga jadi was-was dan ga tenang", "pengendara motor cewek takut lewat situ"],
            'pleas': ["tolong polres dan polsek rutin patroli malam", "mohon aparat keamanan segera bertindak tegas", "tolong pos kamling diaktifkan lagi", "tangkap dan tindak oknum preman meresahkan", "min tolong sampaikan ke babinsa/bhabinkamtibmas", "tolong tertibkan balap liar pak polisi", "mohon pasang lampu jalan dan cctv di titik rawan"]
        },
        'Bansos': {
            'subjects': ["Penyaluran beras bansos", "Bansos PKH dan BPNT", "Bantuan Langsung Tunai (BLT)", "Kartu Sembako Murah", "Data Terpadu Kesejahteraan Sosial (DTKS)", "Bantuan modal UMKM", "Kartu Indonesia Sehat (KIS PBI)", "Bantuan bedah rumah rutilahu", "Bantuan santunan lansia", "Pencairan dana bansos di kantor pos/bank", "Kartu Keluarga Sejahtera (KKS)"],
            'conditions': ["salah sasaran yg mampu dan bermobil malah dapet", "ada pemotongan dana sepihak oleh oknum RT/RW", "kualitas beras berkutu, apek dan hancur", "udah 3 bulan belum cair tanpa penjelasan", "nama dicoret tiba-tiba padahal miskin", "saldo di kartu e-warong kosong melompong", "aplikasi pendaftaran bansos error dan gbs login", "warga difabel/lansia terlewat ga didata", "proses birokrasinya dipersulit petugas kelurahan", "keluarga dan timses kades diprioritaskan"],
            'impacts': ["warga miskin beneran cuma bisa gigit jari", "lansia kurang mampu kesulitan beli beras", "uang bantuan berkurang banyak kepotong pungli", "anak yatim ga bisa beli perlengkapan sekolah", "warga sakit ga bisa berobat krn KIS dinonaktifkan", "warga ngeluh ga ada transparansi", "korban PHK ga dapet perhatian sama sekali"],
            'pleas': ["tolong dinas sosial audit ulang data penerima", "mohon kades/lurah tindak tegas oknum yg motong", "tolong verifikasi lapangan langsung ke rumah warga", "kemensos tolong perbaiki sistem data DTKS", "mohon penyaluran bansos dilakukan secara transparan", "tolong bantu proses pencairan kartu saya min", "tolong usut tuntas pungli bantuan sosial ini"]
        }
    }

    random.seed(42)
    target_per_cat = 300
    all_rows = []

    for cat, comp in cat_components.items():
        existing_texts = df_orig[df_orig['kategori'] == cat]['teks'].tolist() if not df_orig.empty else []
        seen = set(existing_texts)
        generated = []
        needed = target_per_cat - len(existing_texts)
        
        while len(generated) < needed:
            loc = random.choice(locations)
            s = random.choice(comp['subjects'])
            c = random.choice(comp['conditions'])
            imp = random.choice(comp['impacts'])
            p = random.choice(comp['pleas'])
            
            raw = f"{s} di {loc} {c}, {imp}, {p}"
            if raw not in seen:
                seen.add(raw)
                generated.append({'teks': raw, 'kategori': cat})
                
        orig_rows = [{'teks': t, 'kategori': cat} for t in existing_texts]
        all_rows.extend(orig_rows + generated)

    df_final = pd.DataFrame(all_rows)
    df_final = df_final.sample(frac=1, random_state=42).reset_index(drop=True)
    df_final.to_csv(csv_path, index=False)
    print(f"[✓] Dataset {csv_path} berhasil dibuat ({len(df_final)} baris seimbang).")
    return df_final

def main():
    print("=" * 60)
    print("🤖 BEDAS Lapor-AI — Pelatihan & Evaluasi Model NLP Mandiri")
    print("=" * 60)

    # 1. Pastikan dataset siap
    df = ensure_dataset()
    print(f"[*] Distribusi Kelas:\n{df['kategori'].value_counts()}\n")

    # 2. Preprocessing
    print("[*] Melakukan preprocessing teks (cleaning + stopwords)...")
    df['teks_bersih'] = df['teks'].apply(preprocess)

    # 3. Train-Test Split (80:20 Stratified)
    X = df['teks_bersih']
    y = df['kategori']
    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.2, random_state=42, stratify=y
    )

    # 4. Feature Extraction (TF-IDF N-gram 1-2)
    print("[*] Ekstraksi fitur TF-IDF (ngram_range=(1,2), max_features=5000)...")
    vectorizer = TfidfVectorizer(ngram_range=(1, 2), max_features=5000, sublinear_tf=True)
    X_train_tfidf = vectorizer.fit_transform(X_train)
    X_test_tfidf = vectorizer.transform(X_test)

    # 5. Eksperimen Beberapa Algoritma
    models = {
        'Multinomial Naive Bayes': MultinomialNB(alpha=0.1),
        'Calibrated Linear SVM': CalibratedClassifierCV(LinearSVC(C=1.0, random_state=42), cv=3),
        'Logistic Regression': LogisticRegression(C=1.0, max_iter=500, random_state=42)
    }

    best_name = None
    best_score = 0.0
    best_model = None

    print("\n" + "=" * 60)
    print("📊 HASIL EVALUASI MODEL (TRAIN-TEST SPLIT 80:20)")
    print("=" * 60)

    for name, model in models.items():
        model.fit(X_train_tfidf, y_train)
        y_pred = model.predict(X_test_tfidf)
        acc = accuracy_score(y_test, y_pred)
        
        cv_scores = cross_val_score(model, vectorizer.transform(X), y, cv=5)
        cv_mean = cv_scores.mean()

        print(f"\n🔹 Model: {name}")
        print(f"   Accuracy: {acc * 100:.2f}% | 5-Fold CV Score: {cv_mean * 100:.2f}% (+/- {cv_scores.std() * 100:.2f}%)")
        print("   Classification Report:")
        print(classification_report(y_test, y_pred, digits=4))

        if cv_mean > best_score:
            best_score = cv_mean
            best_name = name
            best_model = model

    print("=" * 60)
    print(f"🏆 Model Terbaik Terpilih: {best_name} (Akurasi CV: {best_score * 100:.2f}%)")
    print("=" * 60)

    # 6. Fit ulang pada seluruh dataset 1200 data untuk model final produksi
    print("\n[*] Melatih model final pada seluruh 1.200 sampel dataset...")
    final_vectorizer = TfidfVectorizer(ngram_range=(1, 2), max_features=5000, sublinear_tf=True)
    X_all_tfidf = final_vectorizer.fit_transform(X)
    
    # Gunakan CalibratedClassifierCV(LinearSVC) untuk akurasi tertinggi + support predict_proba
    final_model = CalibratedClassifierCV(LinearSVC(C=1.0, random_state=42), cv=3)
    final_model.fit(X_all_tfidf, y)

    # 7. Ekspor model & vectorizer
    models_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'models')
    os.makedirs(models_dir, exist_ok=True)

    model_path = os.path.join(models_dir, 'model.pkl')
    vec_path = os.path.join(models_dir, 'vectorizer.pkl')

    joblib.dump(final_model, model_path)
    joblib.dump(final_vectorizer, vec_path)

    print(f"[✓] Model tersimpan di: {model_path}")
    print(f"[✓] Vectorizer tersimpan di: {vec_path}")

    # 8. Test Sanity Check
    test_samples = [
        "Jalan aspal di deket jembatan Dayeuhkolot bolong dalem bikin pemotor jatoh",
        "Tumpukan sampah plastik di selokan Bojongsoang meluap bikin banjir pas ujan lebat",
        "Aksi geng motor bawa sajam di Soreang tiap malam meresahkan warga sekitar",
        "Bansos beras PKH salah sasaran orang kaya dapet warga miskin malah dicoret"
    ]

    print("\n" + "=" * 60)
    print("🧪 SANITY CHECK PREDIKSI CONTOH TEKS BARU")
    print("=" * 60)
    for sample in test_samples:
        clean = preprocess(sample)
        vec = final_vectorizer.transform([clean])
        pred = final_model.predict(vec)[0]
        probs = final_model.predict_proba(vec)[0]
        conf = float(np.max(probs))
        print(f"📝 Teks: \"{sample}\"")
        print(f"   ➡️ Kategori Prediksi: [{pred}] (Confidence: {conf * 100:.1f}%)\n")

if __name__ == '__main__':
    main()
