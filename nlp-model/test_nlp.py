import unittest
import os
import sys

# Tambahkan path src
sys.path.append(os.path.join(os.path.dirname(__file__), 'src'))
from preprocessing import preprocess
from predict import predict

class TestNlpPipeline(unittest.TestCase):

    def test_preprocessing_cleans_text_properly(self):
        raw = "Jalan di Majalaya hancur parah!! https://contoh.com #rusak 123"
        clean = preprocess(raw)
        self.assertNotIn("https", clean)
        self.assertNotIn("!!", clean)
        self.assertIn("jalan", clean)
        self.assertIn("majalaya", clean)

    def test_predict_returns_valid_structure(self):
        text = "Jalan raya Soreang rusak berlubang parah sering mencelakakan warga"
        result = predict(text)
        self.assertIn('kategori', result)
        self.assertIn('confidence', result)
        self.assertIn('model', result)
        self.assertGreaterEqual(result['confidence'], 0.0)
        self.assertLessEqual(result['confidence'], 1.0)
        self.assertEqual(result['kategori'], 'Jalan Rusak')

    def test_predict_categories(self):
        cases = [
            ("Sampah menumpuk di selokan menyebabkan banjir cileunyi", "Sampah/Banjir"),
            ("Aksi begal motor dan geng motor bersenjata di margahayu", "Keamanan/Ketertiban"),
            ("Bantuan beras PKH dan BLT bansos belum disalurkan", "Bansos"),
        ]
        for query, expected in cases:
            res = predict(query)
            self.assertEqual(res['kategori'], expected, f"Failed for query: {query}")

if __name__ == '__main__':
    unittest.main()
