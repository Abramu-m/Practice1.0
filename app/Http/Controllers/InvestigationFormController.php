<?php

namespace App\Http\Controllers;

use App\Models\InvestigationForm;
use Illuminate\Http\Request;

class InvestigationFormController extends Controller
{
    // List all forms
    public function index()
    {
        $forms = InvestigationForm::latest()->get();

        // Scan lab/forms/ for blade files not yet registered in the DB
        $dir = resource_path('views/lab/forms');
        $registeredViews = $forms->pluck('blade_view')->flip();
        $availableTemplates = [];
        if (is_dir($dir)) {
            foreach (glob($dir . DIRECTORY_SEPARATOR . '*.blade.php') as $file) {
                $name = pathinfo($file, PATHINFO_FILENAME); // strips .blade.php
                if (!$registeredViews->has($name)) {
                    $availableTemplates[] = $name;
                }
            }
            sort($availableTemplates);
        }

        return view('investigation_forms.index', compact('forms', 'availableTemplates'));
    }

    // Store new form
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'blade_view' => 'required|string|max:255',
        ]);

        $form = InvestigationForm::create($request->all());

        return response()->json([
            'message' => 'Investigation form created successfully',
            'data' => $form
        ]);
    }

    // Show single form
    public function show($id)
    {
        $form = InvestigationForm::findOrFail($id);

        return response()->json($form);
    }

    // Update form
    public function update(Request $request, $id)
    {
        $form = InvestigationForm::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'blade_view' => 'sometimes|required|string|max:255',
        ]);

        $form->update($request->all());

        return response()->json([
            'message' => 'Updated successfully',
            'data' => $form
        ]);
    }

    // Delete form
    public function destroy($id)
    {
        $form = InvestigationForm::findOrFail($id);
        $form->delete();

        return response()->json([
            'message' => 'Deleted successfully'
        ]);
    }
}