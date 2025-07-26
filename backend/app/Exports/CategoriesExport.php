<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;

class CategoriesExport implements FromQuery, WithHeadings, WithMapping, ShouldQueue
{
    use Exportable;

    public function query()
    {
        return Category::query();
    }

    public function headings(): array
    {
        return [
            'id',
            'name',
            'description',
            'is_active',
            'created_at',
        ];
    }

    public function map($category): array
    {
        return [
            $category->id,
            $category->name,
            $category->description,
            $category->is_active ? 'Yes' : 'No',
            $category->created_at->format('Y-m-d H:i:s'),
        ];
    }
}