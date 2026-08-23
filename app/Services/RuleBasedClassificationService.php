<?php

namespace App\Services;

class RuleBasedClassificationService
{
    /**
     * Daftar kata kunci per kategori.
     */
    protected array $categoryKeywords = [
        'Jalan Rusak' => [
            'jalan', 'aspal', 'lubang', 'jembatan', 'trotoar', 'amblas', 'berlubang', 
            'rusak parah', 'jalan raya', 'pju', 'lampu jalan', 'penerangan', 'gorong',
            'retak', 'batu', 'licin', 'aspal mengelupas', 'jalan desa', 'tanggul jalan'
        ],
        'Lingkungan & Drainase' => [
            'sampah', 'bau', 'busuk', 'limbah', 'drainase', 'selokan', 'got', 'sungai',
            'citarum', 'banjir', 'genangan', 'meluap', 'tersumbat', 'plastik', 'kotor',
            'longsor', 'pencemaran', 'polusi', 'debu', 'tumpukan sampah', 'saluran air'
        ],
        'Bantuan Sosial' => [
            'bansos', 'pkh', 'blt', 'sembako', 'dtks', 'bantuan', 'miskin', 'kelaparan',
            'tidak mampu', 'kurang mampu', 'bantuan pangan', 'dana desa', 'yatim', 'lansia',
            'tidak tepat sasaran', 'kartu sembako', 'bantuan tunai'
        ],
        'Keamanan & Ketertiban' => [
            'balap liar', 'tawuran', 'geng motor', 'begal', 'maling', 'pencurian', 'preman',
            'pungli', 'pkl', 'pedagang kaki lima', 'miras', 'narkoba', 'ribut', 'gaduh',
            'kebisingan', 'mabuk', 'meresahkan', 'senjata tajam', 'keamanan', 'ketertiban'
        ],
    ];

    /**
     * Daftar kata kunci urgensi.
     */
    protected array $urgencyKeywords = [
        'Darurat' => [
            'darurat', 'sekarang juga', 'korban jiwa', 'tewas', 'meninggal', 'sekarat',
            'hanyut', 'kebakaran hebat', 'ambruk total', 'tenggelam', 'darah'
        ],
        'Tinggi' => [
            'bahaya', 'kecelakaan', 'amblas', 'jebol', 'putus', 'lumpuh', 'parah',
            'sering celaka', 'rawan', 'malam ini', 'segera', 'mengancam', 'senjata'
        ],
        'Rendah' => [
            'usul', 'saran', 'tanya', 'info', 'mohon info', 'kapan', 'rencana', 'sekadar info'
        ],
    ];

    /**
     * Klasifikasikan teks aduan berdasarkan kata kunci.
     *
     * @return array{kategori: string, confidence: float, urgensi: string, alasan: string, sumber: string}
     */
    public function classify(string $teks): array
    {
        $teksLower = mb_strtolower($teks);
        
        $kategoriScores = [];
        foreach ($this->categoryKeywords as $kategori => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if (str_contains($teksLower, $keyword)) {
                    $score++;
                }
            }
            $kategoriScores[$kategori] = $score;
        }

        arsort($kategoriScores);
        $bestKategori = array_key_first($kategoriScores);
        $bestScore = $kategoriScores[$bestKategori] ?? 0;

        if ($bestScore === 0) {
            $bestKategori = 'Jalan Rusak'; // Default category
            $confidence = 0.50;
        } else {
            $confidence = min(0.85, 0.55 + ($bestScore * 0.10));
        }

        // Tentukan urgensi
        $urgensi = 'Sedang';
        $alasanUrgensi = 'Tingkat urgensi default berdasarkan analisis konteks.';

        foreach ($this->urgencyKeywords['Darurat'] as $keyword) {
            if (str_contains($teksLower, $keyword)) {
                $urgensi = 'Darurat';
                $alasanUrgensi = "Terdeteksi indikasi situasi darurat (kata kunci: '{$keyword}').";
                break;
            }
        }

        if ($urgensi === 'Sedang') {
            foreach ($this->urgencyKeywords['Tinggi'] as $keyword) {
                if (str_contains($teksLower, $keyword)) {
                    $urgensi = 'Tinggi';
                    $alasanUrgensi = "Terdeteksi indikasi potensi bahaya/kerusakan tinggi (kata kunci: '{$keyword}').";
                    break;
                }
            }
        }

        if ($urgensi === 'Sedang') {
            foreach ($this->urgencyKeywords['Rendah'] as $keyword) {
                if (str_contains($teksLower, $keyword)) {
                    $urgensi = 'Rendah';
                    $alasanUrgensi = "Aduan bersifat informasi atau saran.";
                    break;
                }
            }
        }

        return [
            'kategori' => $bestKategori,
            'confidence' => round($confidence, 4),
            'urgensi' => $urgensi,
            'alasan' => $alasanUrgensi,
            'sumber' => 'rule_based_fallback',
        ];
    }
}
