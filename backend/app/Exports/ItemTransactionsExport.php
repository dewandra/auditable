<?php

namespace App\Exports;

use App\Models\ItemTransaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;

class ItemTransactionsExport implements FromQuery, WithHeadings, WithMapping, ShouldQueue
{
    use Exportable;

    public function query()
    {
        return ItemTransaction::query()->with(['item', 'user']);
    }

    public function headings(): array
    {
        return [
            'Transaction ID',
            'Item Name',
            'User',
            'Type',
            'Quantity',
            'Notes',
            'Transaction Date',
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->id,
            $transaction->item->name ?? 'N/A',
            $transaction->user->name ?? 'N/A',
            $transaction->type,
            $transaction->quantity,
            $transaction->notes,
            $transaction->created_at->format('Y-m-d H:i:s'),
        ];
    }
}