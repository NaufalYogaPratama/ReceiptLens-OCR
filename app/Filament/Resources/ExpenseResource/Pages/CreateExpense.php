<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use Filament\Resources\Pages\CreateRecord;
use App\Services\OCRService;
use App\Services\AIParserService;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use App\Jobs\ProcessStrukOcr;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;

    // Kembalikan method ke bentuk semula tanpa argumen
    protected function afterCreate(): void
    {
        // 1. Langsung kirim job ke antrian (queue)
        ProcessStrukOcr::dispatch($this->record);

        // 2. Langsung beri notifikasi ke pengguna bahwa prosesnya sedang berjalan
        Notification::make()
            ->title('Struk diterima!')
            ->body('Struk sedang diproses di latar belakang. Hasilnya akan muncul sesaat lagi.')
            ->success()
            ->send();
    }
}