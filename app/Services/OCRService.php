<?php

namespace App\Services;

use thiagoalessio\TesseractOCR\TesseractOCR;
use Illuminate\Support\Facades\Log;

class OCRService
{
    public function extractTextFromImage(string $path): ?string
    {
        try {
            // Pastikan Tesseract OCR sudah terinstal di sistem operasimu
            return (new TesseractOCR($path))
                ->lang('eng', 'ind') // Coba baca Inggris dan Indonesia
                ->run();
        } catch (\Exception $e) {
            Log::error('Tesseract OCR Gagal: ' . $e->getMessage(), ['path' => $path]);
            return null; // Kembalikan null jika gagal
        }
    }
}