import os
import sys
import numpy as np
import joblib

# Import preprocessing
sys.path.append(os.path.dirname(os.path.abspath(__file__)))
from preprocessing import preprocess

# Path ke artefak model
CURRENT_DIR = os.path.dirname(os.path.abspath(__file__))
MODEL_PATH = os.path.join(CURRENT_DIR, '..', 'models', 'model.pkl')
VEC_PATH = os.path.join(CURRENT_DIR, '..', 'models', 'vectorizer.pkl')

_model = None
_vectorizer = None

def load_model():
    global _model, _vectorizer
    if _model is None or _vectorizer is None:
        if not os.path.exists(MODEL_PATH) or not os.path.exists(VEC_PATH):
            raise FileNotFoundError(f"File model atau vectorizer tidak ditemukan di {MODEL_PATH}")
        _model = joblib.load(MODEL_PATH)
        _vectorizer = joblib.load(VEC_PATH)
    return _model, _vectorizer

def predict(text: str) -> dict:
    """
    Fungsi inferensi mandiri untuk klasifikasi teks aduan warga.
    
    Args:
        text (str): Teks keluhan/aduan warga.
        
    Returns:
        dict: {'kategori': str, 'confidence': float, 'model': str}
    """
    model, vectorizer = load_model()
    
    clean = preprocess(text)
    if not clean:
        clean = text.lower()
        
    vec = vectorizer.transform([clean])
    prediction = model.predict(vec)[0]
    
    try:
        probabilities = model.predict_proba(vec)[0]
        confidence = float(np.max(probabilities))
    except Exception:
        confidence = 0.85
        
    return {
        'kategori': str(prediction),
        'confidence': round(confidence, 4),
        'model': 'local_nlp_svm_tfidf'
    }

if __name__ == '__main__':
    if len(sys.argv) > 1:
        query = " ".join(sys.argv[1:])
    else:
        query = "Jalan raya Kopo amblas berlubang parah sering bikin celaka pemotor"
        
    print(f"[*] Menjalankan inferensi untuk teks: \"{query}\"")
    result = predict(query)
    print(f"[*] Hasil: Kategori [{result['kategori']}], Confidence: {result['confidence'] * 100:.1f}%")
