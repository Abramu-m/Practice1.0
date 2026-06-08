<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\StoreCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoreCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = StoreCategory::withCount('medications')
            ->orderBy('description')
            ->get();
        
        return view('store.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('store.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255|unique:store_categories,description',
        ]);

        $category = StoreCategory::create([
            'description' => $request->description,
        ]);

        return redirect()->route('store-categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(StoreCategory $storeCategory)
    {
        $storeCategory->load('medications');
        
        // Get all items in this category - unified under medications table
        $medications = $storeCategory->medications()->get();
        
        return view('store.categories.show', compact('storeCategory', 'medications'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StoreCategory $storeCategory)
    {
        return view('store.categories.edit', compact('storeCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StoreCategory $storeCategory)
    {
        $request->validate([
            'description' => 'required|string|max:255|unique:store_categories,description,' . $storeCategory->id,
        ]);

        $storeCategory->update([
            'description' => $request->description,
        ]);

        return redirect()->route('store-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StoreCategory $storeCategory)
    {
        // Check if category has items using unified medications table
        if ($storeCategory->medications()->count() > 0) {
            return redirect()->route('store-categories.index')
                ->with('error', 'Cannot delete category with associated items. Please move or delete all items first.');
        }

        $categoryName = $storeCategory->description;
        $storeCategory->delete();

        return redirect()->route('store-categories.index')
            ->with('success', "Category '{$categoryName}' deleted successfully.");
    }

    /**
     * Get categories for API/AJAX
     */
    public function getCategories(Request $request)
    {
        $query = StoreCategory::query();
        
        if ($request->has('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }
        
        $categories = $query->orderBy('description')->get();
        
        return response()->json($categories);
    }

    /**
     * Get category hierarchy
     */
    public function getHierarchy()
    {
        $categories = StoreCategory::orderBy('description')->get();
        
        return response()->json($categories);
    }
}
