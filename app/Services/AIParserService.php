<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AIParserService extends Helper
{
    protected $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function parseWithAI(string $text): ?array
    {
        $apiKey = env('COHERE_API_KEY');
        $prompt = <<<PROMPT
            Ubah teks struk belanja berikut menjadi format JSON tanpa penjelasan tambahan apapun. cukup hasilkan JSON yang berisi informasi penting seperti nama produk, harga, jumlah, dan total belanja.
            
            Contoh format JSON yang diharapkan:
            {
                date: "12 Oct 2023",
                "items": [
                    {
                        "name": "Produk A",
                        "quantity": 2,
                        "price": 10000,
                        "subtotal": 20000
                    },
                    {
                        "name": "Produk B",
                        "quantity": 1,
                        "price": 15000,
                        subtotal: 15000
                    }
                ],
                "total": 35000
                }

                Teks struk belanja:
                $text
            PROMPT;

        $response = \Http::withToken($apiKey)
            ->post('https://api.cohere.ai/v1/generate', [
                'model' => 'command-xlarge-nightly',
                'prompt' => $prompt,
                'max_tokens' => 500,
                'temperature' => 0.2,
            ]);
        
        if (! $response->successful()) {
            return null;
        }

        $raw = $response->json('generations.0.text');
        $onlyJson = $this->cleanCoheretResponse($raw);
        
        return $onlyJson;
    }
}