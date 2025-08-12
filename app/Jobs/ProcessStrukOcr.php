<?php

namespace App\Jobs;

use App\Models\Expense;
use App\Services\AIParserService;
use App\Services\OCRService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessStrukOcr implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Expense $expense)
    {
    }

    // Dependency Injection di method handle() ini BISA berfungsi!
    public function handle(OCRService $ocr, AIParserService $ai): void
    {
        try {
            $record = $this->expense;
            if ($record->receipt_image) {
                $path = storage_path("app/public/{$record->receipt_image}");
                $text = $ocr->extractTextFromImage($path);

                if (empty($text)) return;

                $parsed = $ai->parseWithAI($text);

                if (empty($parsed)) return;

                $record->note = $text;
                $record->date_shopping = $parsed['date'] ?? null;
                $record->amount = $parsed['total'] ?? 0;
                $record->parsed_data = $parsed['items'] ?? [];
                $record->save();

                foreach ($parsed['items'] ?? [] as $item) {
                    $itemData = [
                        'name' => $item['name'] ?? 'Item tidak terbaca',
                        'price' => $item['price'] ?? 0,
                        'subtotal' => $item['subtotal'] ?? 0,
                        'qty' => $item['quantity'] ?? ($item['qty'] ?? 1) 
                    ];

                    Log::info('Mencoba membuat item dengan data:', ['item_data' => $itemData]);
                    $record->items()->create($itemData);
                }
            }
        } catch (\Throwable $th) {
            Log::error('Job ProcessStrukOcr Gagal: ' . $th->getMessage());
        }
    }
}