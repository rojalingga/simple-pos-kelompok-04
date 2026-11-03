<?php

namespace App\Imports;

use App\Models\ImportError;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use Throwable;

class ProductsImport implements SkipsEmptyRows, SkipsOnError, SkipsOnFailure, ToModel, WithChunkReading, WithHeadingRow, WithValidation
{
    use RemembersRowNumber;

    public function __construct(
        private ?int $userId = null,
        private ?string $fileName = null,
    ) {}

    public function model(array $row)
    {
        $product = Product::firstOrNew([
            'name' => $row['name'],
            'category_id' => $row['category_id'],
        ]);

        $product->price = $row['price'];

        return $product;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'integer', 'min:0'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'name.required' => 'Kolom name wajib diisi.',
            'name.string' => 'Kolom name harus berupa teks.',
            'name.max' => 'Kolom name maksimal 255 karakter.',
            'category_id.required' => 'Kolom category_id wajib diisi.',
            'category_id.exists' => 'Kategori dengan ID tersebut tidak ditemukan.',
            'price.required' => 'Kolom price wajib diisi.',
            'price.integer' => 'Kolom price harus berupa angka bulat.',
            'price.min' => 'Kolom price tidak boleh negatif.',
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function onFailure(Failure ...$failures)
    {
        $groupedMessages = [];

        foreach ($failures as $failure) {
            foreach ($failure->errors() as $message) {
                $groupedMessages[$failure->row()][] = $message;
            }
        }

        foreach ($groupedMessages as $rowNumber => $messages) {
            $this->recordError((int) $rowNumber, implode(' ', array_unique($messages)));
        }
    }

    public function onError(Throwable $exception)
    {
        $this->recordError(
            (int) ($this->getRowNumber() ?? 0),
            'Gagal menyimpan produk: '.$exception->getMessage(),
        );
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
