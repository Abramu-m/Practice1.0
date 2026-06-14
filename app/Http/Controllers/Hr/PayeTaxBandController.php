<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\PayeTaxBand;
use Illuminate\Http\Request;

class PayeTaxBandController extends Controller
{
    public function index()
    {
        $bands = PayeTaxBand::orderBy('band_order')->get();

        return view('hr.settings.paye_bands.index', compact('bands'));
    }

    public function create()
    {
        $nextOrder = (int) (PayeTaxBand::max('band_order') ?? 0) + 1;

        return view('hr.settings.paye_bands.create', compact('nextOrder'));
    }

    public function store(Request $request)
    {
        PayeTaxBand::create($this->validateBand($request));

        return redirect()->route('hr.settings.paye-bands.index')->with('success', 'PAYE band added.');
    }

    public function edit(PayeTaxBand $payeBand)
    {
        return view('hr.settings.paye_bands.edit', compact('payeBand'));
    }

    public function update(Request $request, PayeTaxBand $payeBand)
    {
        $payeBand->update($this->validateBand($request));

        return redirect()->route('hr.settings.paye-bands.index')->with('success', 'PAYE band updated.');
    }

    public function destroy(PayeTaxBand $payeBand)
    {
        $payeBand->delete();

        return back()->with('success', 'PAYE band removed.');
    }

    private function validateBand(Request $request): array
    {
        $validated = $request->validate([
            'band_order' => ['required', 'integer', 'min:1'],
            'min_income' => ['required', 'numeric', 'min:0'],
            'max_income' => ['nullable', 'numeric', 'gt:min_income'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}
