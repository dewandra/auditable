<?php

namespace App\Imports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ItemsImport implements ToModel, WithHeadingRow, ShouldQueue, WithChunkReading
{

    use Importable;
    
    /**
    * @param array $row
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Pastikan category_id ada dan valid sebelum membuat item
        if (empty($row['category_id'])) {
            return null;
        }

        return new Item([
            'name'        => $row['name'],
            'category_id' => $row['category_id'],
            'description' => $row['description'] ?? null,
            'quantity'    => $row['quantity'] ?? 0,
            'is_active'   => true,
        ]);
    }

    // Memproses file Excel dalam potongan (chunk) 1000 baris
    public function chunkSize(): int
    {
        return 1000;
    }
}