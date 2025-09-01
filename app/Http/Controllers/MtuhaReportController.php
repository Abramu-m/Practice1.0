<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MtuhaReportService;
use Barryvdh\DomPDF\Facade\Pdf;

class MtuhaReportController extends Controller
{
    protected $service;

    public function __construct(MtuhaReportService $service)
    {
        $this->service = $service;
    }

    public function month(Request $request)
    {
        $year = (int) ($request->input('mwaka') ?? date('Y'));
        $month = (int) ($request->input('mwezi') ?? date('n'));

        $useCache = ! (bool) $request->input('nocache');
    $data = $this->service->buildReport($year, $month, $useCache);
    $data['month_name'] = $this->service->monthName($month);

        // PDF export
        if ($request->has('pdf') && (int) $request->input('pdf') === 1) {
            $html = view('reports.mtuha_month', $data)->render();
            $pdf = Pdf::loadHTML($html)->setPaper('a4', 'landscape');
            $fileName = sprintf('mtuha_%d_%02d.pdf', $year, $month);
            return $pdf->download($fileName);
        }
        return view('reports.mtuha_month', $data);
    }
}
