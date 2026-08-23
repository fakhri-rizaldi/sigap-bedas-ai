import os
import sys
import numpy as np
import joblib
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel

# Import preprocessing
sys.path.append(os.path.dirname(os.path.abspath(__file__)))
from src.preprocessing import preprocess

app = FastAPI(
    title="BEDAS Lapor-AI — Local NLP Microservice",
    description="Microservice Klasifikasi Teks Aduan Warga Berbasis TF-IDF + Calibrated Linear SVM",
    version="1.0.0"
)

# Load Model & Vectorizer
CURRENT_DIR = os.path.dirname(os.path.abspath(__file__))
MODEL_PATH = os.path.join(CURRENT_DIR, 'models', 'model.pkl')
VEC_PATH = os.path.join(CURRENT_DIR, 'models', 'vectorizer.pkl')

model = None
vectorizer = None

def load_artifacts():
    global model, vectorizer
    if os.path.exists(MODEL_PATH) and os.path.exists(VEC_PATH):
        try:
            model = joblib.load(MODEL_PATH)
            vectorizer = joblib.load(VEC_PATH)
            print("[✓] Model dan Vectorizer NLP Lokal berhasil dimuat ke memori.")
        except Exception as e:
            print(f"[!] Gagal memuat model NLP: {e}")
    else:
        print(f"[!] File model.pkl atau vectorizer.pkl belum ditemukan di {CURRENT_DIR}/models/")

load_artifacts()

class PredictRequest(BaseModel):
    text: str

class PredictResponse(BaseModel):
    status: str
    kategori: str
    confidence: float
    model: str

@app.get("/health")
def health_check():
    return {
        "status": "healthy" if (model is not None and vectorizer is not None) else "uninitialized",
        "model_loaded": model is not None,
        "vectorizer_loaded": vectorizer is not None,
        "version": "1.0.0"
    }

@app.post("/predict", response_model=PredictResponse)
def predict_category(req: PredictRequest):
    global model, vectorizer
    if model is None or vectorizer is None:
        load_artifacts()
        if model is None or vectorizer is None:
            raise HTTPException(status_code=503, detail="Model NLP lokal belum siap atau belum dilatih.")

    text = req.text.strip()
    if len(text) < 3:
        raise HTTPException(status_code=422, detail="Teks terlalu pendek untuk dianalisis.")

    # 1. Preprocessing
    clean_text = preprocess(text)
    if not clean_text:
        clean_text = text.lower()

    # 2. Vectorize
    vec = vectorizer.transform([clean_text])

    # 3. Predict & Confidence
    pred_category = model.predict(vec)[0]
    
    try:
        probabilities = model.predict_proba(vec)[0]
        confidence = float(np.max(probabilities))
    except Exception:
        confidence = 0.85

    return {
        "status": "success",
        "kategori": str(pred_category),
        "confidence": round(confidence, 4),
        "model": "local_nlp_svm_tfidf"
    }

if __name__ == '__main__':
    import uvicorn
    print("[*] Menjalankan server FastAPI NLP di http://127.0.0.1:8001 ...")
    uvicorn.run("app:app", host="127.0.0.1", port=8001, reload=False)
