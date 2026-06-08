<?php
namespace App\Http\Controllers;

use App\Models\InvestigationFormData;
use Illuminate\Http\Request;

class InvestigationFormDataController extends Controller
{
    // Store new form data
    public function store(Request $request)
    {
        $request->validate([
            'investigation_id' => 'required|exists:investigations,id',
            'form_data' => 'required|array',
        ]);

        $data = InvestigationFormData::create([
            'investigation_id' => $request->investigation_id,
            'form_data' => $request->form_data,
        ]);

        return response()->json([
            'message' => 'Investigation form data saved successfully',
            'data' => $data
        ]);
    }

    // Show all form data for an investigation
    public function showByInvestigation($investigationId)
    {
        $data = InvestigationFormData::where('investigation_id', $investigationId)->get();

        return response()->json($data);
    }

    // Delete a record
    public function destroy($id)
    {
        $record = InvestigationFormData::findOrFail($id);
        $record->delete();

        return response()->json([
            'message' => 'Deleted successfully'
        ]);
    }
}
