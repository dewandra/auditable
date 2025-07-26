<?php

namespace App\Http\Controllers\Api;

use App\Exports\ItemsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Item\StoreItemRequest;
use App\Http\Requests\Item\UpdateItemRequest;
use App\Http\Resources\AuditResource;
use App\Http\Resources\ItemResource;
use App\Imports\ItemsImport;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $items = Item::query()
            // Eager load relasi untuk efisiensi
            ->with('category')
            // Pencarian berdasarkan nama atau deskripsi
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('description', 'like', "%{$s}%"))
            // Filter berdasarkan kategori
            ->when($request->category_id, fn($q, $id) => $q->where('category_id', $id))
            ->latest()
            ->paginate(10);

        return ItemResource::collection($items);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreItemRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('item_files', 'public');
        }

        $item = Item::create($data);
        return new ItemResource($item);
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        return new ItemResource($item->load('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateItemRequest $request, Item $item)
    {
        $data = $request->validated();

        if ($request->hasFile('file')) {
            if ($item->file_path) {
                Storage::disk('public')->delete($item->file_path);
            }
            $data['file_path'] = $request->file('file')->store('item_files', 'public');
        }

        $item->update($data);
        return new ItemResource($item);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        if ($item->file_path) {
            Storage::disk('public')->delete($item->file_path);
        }

        $item->delete();

        return response()->noContent();
    }

    public function history($itemId) 
    {
        $item = Item::withTrashed()->find($itemId);

        if (!$item) {
            return response()->json(['message' => 'Item not found.'], 404);
        }

        $audits = $item->audits()->with('user')->latest()->get();

        return AuditResource::collection($audits);
    }

    public function export(Request $request)
    {
        // Validasi agar hanya field yang diizinkan yang bisa diekspor
        $request->validate([
            'fields' => ['required', 'array'],
            'fields.*' => ['in:id,name,category,quantity,is_active,created_at'],
            'category_id' => ['nullable', 'uuid', 'exists:categories,id']
        ]);

        $fields = $request->input('fields');
        $categoryId = $request->input('category_id');
        $fileName = 'items_export_' . time() . '.xlsx';

        // Menjalankan proses export di background (queue)
        (new ItemsExport($fields, $categoryId))->queue($fileName, 'public');

        // Memberikan respons langsung ke frontend
        return response()->json([
            'message' => 'Proses ekspor sedang berjalan di background.',
            'download_url' => asset('storage/' . $fileName)
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls']
        ]);

        // Menjalankan proses import di background (queue)
        (new ItemsImport)->queue($request->file('file'));

        return response()->json(['message' => 'Proses impor sedang berjalan di background.']);
    }
}
