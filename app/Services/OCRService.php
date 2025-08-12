<?php

namespace App\Services;

class OCRService
{
    public function extractTextFromImage(string $path): string
    {
        return (new TesseractOCR($path))
            ->lang('eng')
            ->run();
    }
}