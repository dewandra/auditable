<?php

namespace App\Http\Controllers\Api;

use App\Exports\ItemTransactionsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ItemTransaction\StoreItemTransactionRequest;
use App\Http\Resources\AuditResource;
use App\Http\Resources\ItemTransactionResource;
use App\Models\Item;
use App\Models\ItemTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ItemTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $transactions = ItemTransaction::with(['item', 'user'])
            ->when($request->item_id, fn($q, $id) => $q->where('item_id', $id))
            ->latest()
            ->paginate(15);

        return ItemTransactionResource::collection($transactions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreItemTransactionRequest $request)
    {
        $data = $request->validated();
        // Otomatis tambahkan ID user yang sedang login
        $data['user_id'] = Auth::id();

        try {
            $transaction = DB::transaction(function () use ($data, $request) {
                if ($request->hasFile('attachment')) {
                    $data['attachment'] = $request->file('attachment')->store('attachments', 'public');
                }

                // 1. Buat record transaksi baru
                $transactionRecord = ItemTransaction::create($data);

                // 2. Update kuantitas di tabel 'items'
                $item = Item::find($data['item_id']);
                if ($data['type'] === 'in') {
                    $item->increment('quantity', $data['quantity']);
                } else { // type 'out'
                    $item->decrement('quantity', $data['quantity']);
                }

                return $transactionRecord;
            });

            return new ItemTransactionResource($transaction);

        } catch (\Throwable $th) {
                    return response()->json([
            'message' => 'Terjadi kesalahan.',
            'error' => $th->getMessage(), // <-- INI AKAN MENUNJUKKAN ERROR ASLI
            'file' => $th->getFile(),
            'line' => $th->getLine(),
        ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ItemTransaction $itemTransaction)
    {
        return new ItemTransactionResource($itemTransaction->load(['item', 'user']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function history($transactionId)
    {
        $transaction = \App\Models\ItemTransaction::withTrashed()->find($transactionId);

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found.'], 404);
        }

        $audits = $transaction->audits()->with('user')->latest()->get();

        return \App\Http\Resources\AuditResource::collection($audits);
    }   

     public function export(Request $request)
    {
        $fileName = 'transactions_export_' . time() . '.xlsx';
        (new ItemTransactionsExport)->queue($fileName, 'public');
        return response()->json([
            'message' => 'Proses ekspor transaksi sedang berjalan di background.',
            'download_url' => asset('storage/' . $fileName)
        ]);
    }
}
