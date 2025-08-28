<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\StoreSupplier;
use Illuminate\Http\Request;

class StoreSupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suppliers = StoreSupplier::withCount(['goodsReceivedNotes'])
            ->orderBy('name')
            ->get()
            ->map(function ($supplier) {
                $supplier->pending_amount = $supplier->getPendingAmount();
                $supplier->credit_exceeded = $supplier->isCreditExceeded();
                return $supplier;
            });
        
        return view('medications.stock.suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('medications.stock.suppliers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:store_suppliers,name',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_number' => 'nullable|string|max:50',
            'license_number' => 'nullable|string|max:100',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days' => 'nullable|integer|min:0|max:365',
            'payment_terms' => 'nullable|string|max:100',
            'is_active' => 'boolean'
        ]);

        $supplier = StoreSupplier::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'tax_number' => $request->tax_number,
            'license_number' => $request->license_number,
            'credit_limit' => $request->credit_limit ?? 0,
            'credit_days' => $request->credit_days ?? 30,
            'payment_terms' => $request->payment_terms ?? 'credit',
            'is_active' => $request->boolean('is_active', true)
        ]);

        return redirect()->route('medications.stock.suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(StoreSupplier $supplier)
    {
        $supplier->load(['goodsReceivedNotes']);
        
        $recentGRNs = $supplier->goodsReceivedNotes()
            ->latest()
            ->take(10)
            ->with('receivedBy')
            ->get();
        
        $pendingAmount = $supplier->getPendingAmount();
        $creditExceeded = $supplier->isCreditExceeded();
        
        return view('medications.stock.suppliers.show', compact(
            'supplier', 
            'recentGRNs', 
            'pendingAmount', 
            'creditExceeded',
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StoreSupplier $supplier)
    {
        return view('medications.stock.suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StoreSupplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:store_suppliers,name,' . $supplier->id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_number' => 'nullable|string|max:50',
            'license_number' => 'nullable|string|max:100',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days' => 'nullable|integer|min:0|max:365',
            'payment_terms' => 'nullable|string|max:100',
            'is_active' => 'boolean'
        ]);

        $supplier->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'tax_number' => $request->tax_number,
            'license_number' => $request->license_number,
            'credit_limit' => $request->credit_limit ?? 0,
            'credit_days' => $request->credit_days ?? 30,
            'payment_terms' => $request->payment_terms ?? 'credit',
            'is_active' => $request->boolean('is_active', true)
        ]);

        return redirect()->route('medications.stock.suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StoreSupplier $supplier)
    {
        // Check if supplier has GRNs
        if ($supplier->goodsReceivedNotes()->count() > 0) {
            return back()->withErrors(['delete' => 'Cannot delete supplier with associated GRNs.']);
        }
        
        $supplier->delete();

        return redirect()->route('medications.stock.suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }

    /**
     * Toggle supplier status
     */
    public function toggleStatus(StoreSupplier $supplier)
    {
        $supplier->update(['is_active' => !$supplier->is_active]);
        
        $status = $supplier->is_active ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "Supplier {$status} successfully.");
    }

    /**
     * Get suppliers for API/AJAX
     */
    public function getSuppliers(Request $request)
    {
        $query = StoreSupplier::active();
        
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }
        
        $suppliers = $query->orderBy('name')->get();
        
        return response()->json($suppliers);
    }

    /**
     * Get supplier performance data
     */
    public function performance(StoreSupplier $supplier)
    {
        $totalGRNs = $supplier->goodsReceivedNotes()->count();
        $totalAmount = $supplier->goodsReceivedNotes()->sum('total_amount');
        $averageAmount = $totalGRNs > 0 ? $totalAmount / $totalGRNs : 0;
        
        // Monthly performance
        $monthlyData = $supplier->goodsReceivedNotes()
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as grn_count, SUM(total_amount) as total_amount')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(12)
            ->get();
        
        // Payment performance
        $paidOnTime = $supplier->goodsReceivedNotes()
            ->where('status', 'paid')
            ->whereRaw('DATEDIFF(updated_at, created_at) <= payment_terms')
            ->count();
        
        $paymentPerformance = $totalGRNs > 0 ? ($paidOnTime / $totalGRNs) * 100 : 0;
        
        return response()->json([
            'total_grns' => $totalGRNs,
            'total_amount' => $totalAmount,
            'average_amount' => $averageAmount,
            'monthly_data' => $monthlyData,
            'payment_performance' => $paymentPerformance,
            'pending_amount' => $supplier->getPendingAmount(),
            'credit_utilization' => $supplier->credit_limit > 0 ? ($supplier->getPendingAmount() / $supplier->credit_limit) * 100 : 0
        ]);
    }
}
