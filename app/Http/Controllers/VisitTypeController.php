<?php

namespace App\Http\Controllers;

use App\Models\VisitType;
use Illuminate\Http\Request;

class VisitTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $visitTypes = VisitType::all();
        return view('visit_types.index', compact('visitTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('visit_types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255|unique:visit_types,description',
        ]);

        VisitType::create($request->all());

        return redirect()->route('visit_types.index')
                         ->with('success', 'Visit Type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(VisitType $visitType)
    {
        return view('visit_types.show', compact('visitType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VisitType $visitType)
    {
        return view('visit_types.edit', compact('visitType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VisitType $visitType)
    {
        $request->validate([
            'description' => 'required|string|max:255|unique:visit_types,description,' . $visitType->id,
        ]);

        $visitType->update($request->all());

        return redirect()->route('visit_types.index')
                         ->with('success', 'Visit Type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VisitType $visitType)
    {
        $visitType->delete();

        return redirect()->route('visit_types.index')
                         ->with('success', 'Visit Type deleted successfully.'); 
    }
}
