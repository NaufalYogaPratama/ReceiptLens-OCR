<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIParserService extends Helper
{
    protected $apiKey;

    // Kita tidak butuh constructor jika API Key diambil dari Service Provider
    // public function __construct(string $apiKey)
    // {
    //     $this->apiKey = $apiKey;
    // }

    public function parseWithAI(string $text): ?array
    {
        // Ambil API key dari file .env
        $apiKey = config('services.cohere.key');
        if (!$apiKey) {
            Log::error('COHERE_API_KEY tidak ditemukan.');
            return null;
        }

        // Prompt tidak perlu Heredoc jika sederhana, ini lebih aman
        $prompt = <<<PROMPT
Ubah teks struk belanja berikut menjadi format JSON.
Hanya ekstrak informasi berikut: tanggal transaksi, daftar item yang dibeli (nama, jumlah, harga satuan, subtotal), dan total akhir belanja.

PENTING:
1. Abaikan semua teks yang bukan merupakan item belanja, seperti nama toko, alamat, info kasir, subtotal, PPN/VAT, info pembayaran (tunai/kembalian), dan ucapan terima kasih.
2. Pastikan format JSON yang dihasilkan valid dan tidak ada penjelasan tambahan apapun.

Contoh format JSON yang diharapkan:
{
    "date": "10/01/2024",
    "items": [
        { "name": "Teh", "qty": 2, "price": 5000, "subtotal": 10100 },
        { "name": "Muscat cookie", "qty": 1, "price": 10000, "subtotal": 10000 }
    ],
    "total": 22110
}

Teks struk belanja:
{$text}
PROMPT;

        $response = Http::withToken($apiKey)
            ->timeout(60) // Tambahkan timeout untuk mencegah request gantung
            ->post('https://api.cohere.ai/v1/generate', [
                'model' => 'command', // Model yang lebih umum dan stabil
                'prompt' => $prompt,
                'max_tokens' => 1024, // Perbesar token untuk struk panjang
                'temperature' => 0.1,
            ]);
        
        if (! $response->successful()) {
            Log::error('Panggilan ke Cohere API gagal', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return null;
        }

        $raw = $response->json('generations.0.text');
        
        if (!$raw) {
            return null;
        }

        // Panggil nama method yang sudah diperbaiki
        $onlyJson = $this->cleanCohereResponse($raw);
        
        return $onlyJson;
    }
}