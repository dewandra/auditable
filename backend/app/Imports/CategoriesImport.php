<?php

namespace App\Imports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class CategoriesImport implements ToModel, WithHeadingRow, ShouldQueue, WithChunkReading
{

    use Importable;
    public function model(array $row)
    {
        return new Category([
            'name'        => $row['name'],
            'description' => $row['description'] ?? null,
            'is_active'   => true,
        ]);
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}