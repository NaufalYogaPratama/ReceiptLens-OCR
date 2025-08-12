<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class Helper
{
    // Ubah nama method agar konsisten (Cohere bukan Coherent)
    public function cleanCohereResponse(string $responseText): ?array
    {
        // Cari posisi kurung kurawal pembuka pertama
        $firstBrace = strpos($responseText, '{');
        // Cari posisi kurung kurawal penutup terakhir
        $lastBrace = strrpos($responseText, '}');

        // Jika salah satunya tidak ditemukan, proses tidak bisa lanjut
        if ($firstBrace === false || $lastBrace === false) {
            Log::warning('JSON tidak ditemukan dalam respons AI.', ['response' => $responseText]);
            return null;
        }

        // Ambil string di antara kurung kurawal pertama dan terakhir
        $cleaned = substr($responseText, $firstBrace, $lastBrace - $firstBrace + 1);

        if (empty(trim($cleaned))) {
            return null;
        }

        try {
            return json_decode($cleaned, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            Log::warning('Gagal decode JSON AI: ' . $e->getMessage(), [
                'response' => $responseText,
                'cleaned' => $cleaned,
            ]);
            return null;
        }
    }
}