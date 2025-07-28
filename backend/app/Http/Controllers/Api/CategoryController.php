<?php

namespace App\Http\Controllers\Api;

use App\Exports\CategoriesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\AuditResource;
use App\Http\Resources\CategoryResource;
use App\Imports\CategoriesImport;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categories = Category::query()
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('description', 'like', "%{$s}%"))
            ->latest()
            ->paginate(10);

        return CategoryResource::collection($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create($request->validated());
        return new CategoryResource($category);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());
        return new CategoryResource($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->noContent();
    }

    public function history($categoryId)
    {
        $category = \App\Models\Category::withTrashed()->find($categoryId);

        if (!$category) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        $audits = $category->audits()->with('user')->latest()->get();

        return \App\Http\Resources\AuditResource::collection($audits);
    }

    public function export(Request $request)
    {
        // Membuat nama file yang unik berdasarkan waktu
        $fileName = 'categories_export_' . time() . '.xlsx';

        // Menjalankan proses pembuatan file Excel di background menggunakan queue
        // File akan disimpan di dalam 'storage/app/public'
        (new CategoriesExport)->queue($fileName, 'public');

        // Mengembalikan respons ke frontend secara langsung
        return response()->json([
            'message' => 'Proses ekspor kategori sedang berjalan di background.',
            // Memberikan URL yang akan digunakan frontend untuk mengunduh file setelah selesai
            'download_url' => Storage::disk('public')->url($fileName)
        ]);
    }

    public function import(Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls']]);
        (new CategoriesImport)->queue($request->file('file'));
        return response()->json(['message' => 'Proses impor kategori sedang berjalan di background.']);
    }
    
}
