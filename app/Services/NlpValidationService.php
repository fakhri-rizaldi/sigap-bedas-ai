<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NlpValidationService
{
    protected string $microserviceUrl;
    protected int $timeout;
    protected RuleBasedClassificationService $fallbackService;

    public function __construct(RuleBasedClassificationService $fallbackService)
    {
        $this->microserviceUrl = config('services.nlp_microservice.url', env('NLP_MICROSERVICE_URL', 'http://127.0.0.1:8001/predict'));
        $this->timeout = (int) config('services.nlp_microservice.timeout', env('NLP_MICROSERVICE_TIMEOUT', 3));
        $this->fallbackService = $fallbackService;
    }

    /**
     * Memprediksi kategori aduan menggunakan Model NLP Mandiri (FastAPI).
     *
     * @param string $text Teks aduan warga
     * @return array{kategori: string, confidence: float, sumber: string, status: string}
     */
    public function predict(string $text): array
    {
        try {
            $response = Http::connectTimeout(1)->timeout($this->timeout)->post($this->microserviceUrl, [
                'text' => $text,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['kategori']) && isset($data['confidence'])) {
                    return [
                        'kategori' => (string) $data['kategori'],
                        'confidence' => (float) $data['confidence'],
                        'sumber' => 'model_lokal',
                        'status' => 'success',
                    ];
                }
            }

            Log::warning("FastAPI NLP Microservice merespons tidak standar ({$response->status()}): {$response->body()}");
        } catch (\Throwable $e) {
            Log::info("FastAPI NLP Microservice tidak dapat dijangkau ({$e->getMessage()}), menggunakan fallback rules.");
        }

        // Fallback ke RuleBasedClassificationService jika microservice offline
        $fallback = $this->fallbackService->classify($text);
        return [
            'kategori' => $fallback['kategori'],
            'confidence' => 0.75,
            'sumber' => 'model_lokal_fallback',
            'status' => 'fallback',
        ];
    }
}
