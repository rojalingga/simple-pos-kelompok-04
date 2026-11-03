<?php

namespace App\Jobs;

use App\Models\ImportError;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ProcessProductImport implements ShouldQueue
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
            $this->processFile($path);
        } finally {
            Storage::delete($this->csvPath);
        }
    }

    private function processFile(string $path): void
    {
        $lines = file($path, FILE_IGNORE_NEW_LINES);

        if ($lines === false || $lines === []) {
            $this->recordError(1, 'File CSV kosong.');

            return;
        }

        $rows = array_map('str_getcsv', $lines);
        $header = array_shift($rows);

        if ($header === null) {
            $this->recordError(1, 'Header CSV tidak ditemukan.');

            return;
        }

        $header = array_map(fn ($column) => trim((string) $column), $header);
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) ($header[0] ?? ''));
        $expectedColumns = count($header);

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            if ($this->isEmptyRow($row)) {
                continue;
            }

            if (count($row) !== $expectedColumns) {
                $this->recordError(
                    $rowNumber,
                    sprintf(
                        'Jumlah kolom tidak sesuai: diharapkan %d, ditemukan %d.',
                        $expectedColumns,
                        count($row),
                    ),
                );

                continue;
            }

            $data = array_combine($header, $row);

            if ($data === false) {
                $this->recordError($rowNumber, 'Baris tidak dapat dibaca.');

                continue;
            }

            $validator = Validator::make($data, [
                'name' => ['required', 'string', 'max:255'],
                'category_id' => ['required', 'exists:categories,id'],
                'price' => ['required', 'integer', 'min:0'],
            ], [
                'name.required' => 'Kolom name wajib diisi.',
                'name.string' => 'Kolom name harus berupa teks.',
                'name.max' => 'Kolom name maksimal 255 karakter.',
                'category_id.required' => 'Kolom category_id wajib diisi.',
                'category_id.exists' => 'Kategori dengan ID tersebut tidak ditemukan.',
                'price.required' => 'Kolom price wajib diisi.',
                'price.integer' => 'Kolom price harus berupa angka bulat.',
                'price.min' => 'Kolom price tidak boleh negatif.',
            ]);

            if ($validator->fails()) {
                $this->recordError(
                    $rowNumber,
                    implode(' ', $validator->errors()->all()),
                );

                continue;
            }

            $validated = $validator->validated();

            try {
                Product::updateOrCreate(
                    [
                        'name' => $validated['name'],
                        'category_id' => $validated['category_id'],
                    ],
                    [
                        'price' => $validated['price'],
                    ]
                );
            } catch (Throwable $exception) {
                $this->recordError(
                    $rowNumber,
                    'Gagal menyimpan produk: '.$exception->getMessage(),
                );
            }
        }
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function recordError(int $rowNumber, string $message): void
    {
        ImportError::create([
            'user_id' => $this->userId,
            'file_name' => $this->fileName,
            'row_number' => $rowNumber,
            'message' => $message,
        ]);
    }
}
