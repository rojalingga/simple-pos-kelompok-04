<?php

namespace App\Jobs;

use App\Imports\ProductsImport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Excel as ExcelReader;
use Maatwebsite\Excel\Facades\Excel;

class ProcessProductImportExcel implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [10, 60];

    public int $timeout = 60;

    public function __construct(
        private string $csvPath,
        private ?int $userId = null,
        private ?string $fileName = null,
    ) {}

    public function handle(): void
    {
        $path = Storage::path($this->csvPath);

        if (! is_file($path)) {
            return;
        }

        try {
            Excel::import(
                new ProductsImport($this->userId, $this->fileName),
                $path,
                null,
                ExcelReader::CSV,
            );
        } finally {
            Storage::delete($this->csvPath);
        }
    }
}
