<?php

namespace App\Exports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;

class ItemsExport implements FromQuery, WithHeadings, WithMapping, ShouldQueue
{
    use Exportable;

    protected $selectedFields;
    protected $categoryId;

    // Constructor untuk menerima field dinamis dan filter dari controller
    public function __construct(array $selectedFields, $categoryId = null)
    {
        $this->selectedFields = $selectedFields;
        $this->categoryId = $categoryId;
    }

    // Mengambil data dari database dengan filter
    public function query()
    {
        return Item::query()
            ->with('category')
            ->when($this->categoryId, fn($q) => $q->where('category_id', $this->categoryId));
    }

    // Menentukan header kolom berdasarkan field yang dipilih
    public function headings(): array
    {
        return $this->selectedFields;
    }

    // Memetakan setiap baris data ke kolom yang sesuai
    public function map($item): array
    {
        $row = [];
        foreach ($this->selectedFields as $field) {
            switch ($field) {
                case 'id':
                    $row[] = $item->id;
                    break;
                case 'name':
                    $row[] = $item->name;
                    break;
                case 'category':
                    $row[] = $item->category->name ?? 'N/A';
                    break;
                case 'quantity':
                    $row[] = $item->quantity;
                    break;
                case 'is_active':
                    $row[] = $item->is_active ? 'Yes' : 'No';
                    break;
                case 'created_at':
                    $row[] = $item->created_at->format('Y-m-d H:i:s');
                    break;
            }
        }
        return $row;
    }
}