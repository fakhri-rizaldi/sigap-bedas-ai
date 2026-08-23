<?php

namespace App\Jobs;

use App\Models\Aduan;
use App\Services\NlpValidationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ValidasiKlasifikasiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Aduan $aduan;

    /**
     * Create a new job instance.
     */
    public function __construct(Aduan $aduan)
    {
        $this->aduan = $aduan;
    }

    /**
     * Execute the job: Validasi silang hasil Gemini AI vs Model NLP Lokal.
     */
    public function handle(NlpValidationService $nlpService): void
    {
        // Ambil prediksi dari Model NLP Mandiri
        $localResult = $nlpService->predict($this->aduan->teks_aduan);

        $kategoriLokal = $localResult['kategori'];
        $confidenceLokal = $localResult['confidence'];

        // Cek kesepakatan antar model (Discrepancy Check)
        $isCategoryMatch = (strcasecmp(trim($this->aduan->kategori), trim($kategoriLokal)) === 0);
        $isConfidenceLow = (($this->aduan->confidence_kategori ?? 1.0) < 0.70);

        // Jika berbeda kategori atau tingkat keyakinan rendah -> tandai perlu review staf
        $perluReview = (!$isCategoryMatch || $isConfidenceLow);

        $this->aduan->update([
            'kategori_model_lokal' => $kategoriLokal,
            'confidence_model_lokal' => $confidenceLokal,
            'perlu_review' => $perluReview,
        ]);

        if ($perluReview) {
            Log::info("Aduan #{$this->aduan->kode_tiket} ditandai PERLU REVIEW (Gemini: '{$this->aduan->kategori}', Model Lokal: '{$kategoriLokal}')");
        }
    }
}
