<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GeminiClassificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AduanClassificationController extends Controller
{
    protected GeminiClassificationService $classificationService;

    public function __construct(GeminiClassificationService $classificationService)
    {
        $this->classificationService = $classificationService;
    }

    /**
     * Endpoint klasifikasi aduan warga real-time.
     * POST /api/aduan/classify
     */
    public function classify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'teks_aduan' => ['required', 'string', 'min:5', 'max:2000'],
        ], [
            'teks_aduan.required' => 'Teks aduan wajib diisi.',
            'teks_aduan.min' => 'Teks aduan minimal 5 karakter untuk dianalisis.',
            'teks_aduan.max' => 'Teks aduan maksimal 2000 karakter.',
        ]);

        $result = $this->classificationService->classify($validated['teks_aduan']);

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }
}
