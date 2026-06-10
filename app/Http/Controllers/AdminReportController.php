<?php

namespace App\Http\Controllers;

use App\Services\MalariaVipimoReportService;
use App\Services\MalariaWeeklySurveillanceReportService;
use App\Services\IdSRReportService;
use App\Services\STDSTIReportService;
use App\Services\MedicineReportService;
use App\Services\DTCReportService;
use App\Services\LabReportService;
use App\Services\LabHematologyReportService;
use App\Services\LabBloodTransfusionReportService;
use App\Services\LabClinicalChemistryReportService;
use App\Services\LabMicrobiologyReportService;
use App\Services\LabSerologyReportService;
use App\Services\LabParasitologyReportService;
use App\Services\AluReportService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminReportController extends Controller
{
    protected $malariaVipimoService;
    protected $malariaWeeklySurveillanceService;
    protected $idsrService;
    protected $stiService;
    protected $medicineService;
    protected $dtcService;
    protected $labService;
    protected $labHematologyService;
    protected $labBloodTransfusionService;
    protected $labClinicalChemistryService;
    protected $labMicrobiologyService;
    protected $labSerologyService;
    protected $labParasitologyService;
    protected $aluReportService;

    public function __construct(
        MalariaVipimoReportService $malariaVipimoService,
        MalariaWeeklySurveillanceReportService $malariaWeeklySurveillanceService,
        IdSRReportService $idsrService,
        STDSTIReportService $stiService,
        MedicineReportService $medicineService,
        DTCReportService $dtcService,
        LabReportService $labService,
        LabHematologyReportService $labHematologyService,
        LabBloodTransfusionReportService $labBloodTransfusionService,
        LabClinicalChemistryReportService $labClinicalChemistryService,
        LabMicrobiologyReportService $labMicrobiologyService,
        LabSerologyReportService $labSerologyService,
        LabParasitologyReportService $labParasitologyService,
        AluReportService $aluReportService
    ) {
        $this->malariaVipimoService  = $malariaVipimoService;
        $this->malariaWeeklySurveillanceService = $malariaWeeklySurveillanceService;
        $this->idsrService = $idsrService;
        $this->stiService = $stiService;
        $this->medicineService = $medicineService;
        $this->dtcService = $dtcService;
        $this->labService = $labService;
        $this->labHematologyService = $labHematologyService;
        $this->labBloodTransfusionService = $labBloodTransfusionService;
        $this->labClinicalChemistryService = $labClinicalChemistryService;
        $this->labMicrobiologyService = $labMicrobiologyService;
        $this->labSerologyService = $labSerologyService;
        $this->labParasitologyService = $labParasitologyService;
        $this->aluReportService = $aluReportService;
    }

    /**
     * Reports dashboard index
     */
    public function index()
    {
        return view('admin.reports.index');
    }

    /**
     * IDSR weekly report
     */
    public function idsrWeekly(Request $request)
    {
        $year = (int) ($request->input('year') ?? date('Y'));
        $week = (int) ($request->input('week') ?? date('W'));

        $this->idsrService->setWeeklyDates($year, $week);
        $data = $this->idsrService->buildReport();

        $data['year'] = $year;
        $data['week'] = $week;

        if ($request->has('pdf')) {
            return $this->downloadPdf('idsr-weekly', $data);
        }

        return view('admin.reports.idsr-weekly', $data);
    }

    /**
     * Weekly malaria surveillance report
     */
    public function malariaWeeklySurveillance(Request $request)
    {
        $year = (int) ($request->input('year') ?? date('Y'));
        $week = (int) ($request->input('week') ?? date('W'));

        $this->malariaWeeklySurveillanceService->setWeeklyDates($year, $week);
        $data = $this->malariaWeeklySurveillanceService->buildReport();

        $data['year'] = $year;
        $data['week'] = $week;

        if ($request->has('pdf')) {
            return $this->downloadPdf('malaria-weekly-surveillance', $data);
        }

        return view('admin.reports.malaria-weekly-surveillance', $data);
    }

    /**
     * STD/STI monthly report
     */
    public function stdStiMonthly(Request $request)
    {
        $year = (int) ($request->input('year') ?? date('Y'));
        $month = (int) ($request->input('month') ?? date('n'));

        $this->stiService->setMonthlyDates($year, $month);
        $data = $this->stiService->buildReport();

        $data['year'] = $year;
        $data['month'] = $month;
        $data['month_name'] = Carbon::createFromDate($year, $month, 1)->format('F');

        if ($request->has('pdf')) {
            return $this->downloadPdf('std-sti-monthly', $data);
        }

        return view('admin.reports.std-sti-monthly', $data);
    }

    /**
     * Medicines monthly dispensing report (Taarifa ya Mwezi ya Kutolea Dawa)
     */
    public function medicinesMonthly(Request $request)
    {
        $year = (int) ($request->input('year') ?? date('Y'));
        $month = (int) ($request->input('month') ?? date('n'));

        $this->medicineService->setMonthlyDates($year, $month);
        $data = $this->medicineService->buildReport();

        $data['year'] = $year;
        $data['month'] = $month;
        $data['month_name'] = Carbon::createFromDate($year, $month, 1)->format('F');

        if ($request->has('pdf')) {
            $html = view('admin.reports.pdfs.medicines-monthly', $data)->render();
            $pdf  = Pdf::loadHTML($html)->setPaper('a4', 'portrait');
            return $pdf->download('medicines-monthly-' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.pdf');
        }

        return view('admin.reports.medicines-monthly', $data);
    }

    /**
     * ALu report (Taarifa ya Mwezi ya Dawa za Matibabu ya Malaria)
     */
    public function aluMonthly(Request $request)
    {
        $year = (int) ($request->input('year') ?? date('Y'));
        $month = (int) ($request->input('month') ?? date('n'));

        $this->aluReportService->setMonthlyDates($year, $month);
        $data = $this->aluReportService->buildReport();

        $data['year'] = $year;
        $data['month'] = $month;
        $data['month_name'] = Carbon::createFromDate($year, $month, 1)->format('F');

        if ($request->has('pdf')) {
            $html = view('admin.reports.pdfs.alu-monthly', $data)->render();
            $pdf  = Pdf::loadHTML($html)->setPaper('a4', 'portrait');
            return $pdf->download('alu-monthly-' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.pdf');
        }

        return view('admin.reports.alu-monthly', $data);
    }

    /**
     * Tracer medicines report
     */
    public function tracerMedicines(Request $request)
    {
        $year = (int) ($request->input('year') ?? date('Y'));
        $month = (int) ($request->input('month') ?? date('n'));

        $this->medicineService->setMonthlyDates($year, $month);
        $data = $this->medicineService->buildTracerReport();

        $data['year']       = $year;
        $data['month']      = $month;
        $data['month_name'] = Carbon::createFromDate($year, $month, 1)->format('F');

        if ($request->has('pdf')) {
            return $this->downloadPdf('tracer-medicines', $data);
        }

        return view('admin.reports.tracer-medicines', $data);
    }

    /**
     * Low stock medicines report
     */
    public function lowStockMedicines(Request $request)
    {
        $data = $this->medicineService->buildLowStockReport();

        if ($request->has('pdf')) {
            return $this->downloadPdf('low-stock-medicines', $data);
        }

        return view('admin.reports.low-stock-medicines', $data);
    }

    /**
     * DTC (Diarrhea Treatment Center) monthly report
     */
    public function dtcMonthly(Request $request)
    {
        $year = (int) ($request->input('year') ?? date('Y'));
        $month = (int) ($request->input('month') ?? date('n'));

        $this->dtcService->setMonthlyDates($year, $month);
        $data = $this->dtcService->buildReport();

        $data['year'] = $year;
        $data['month'] = $month;
        $data['month_name'] = Carbon::createFromDate($year, $month, 1)->format('F');

        if ($request->has('pdf')) {
            return $this->downloadPdf('dtc-monthly', $data);
        }

        return view('admin.reports.dtc-monthly', $data);
    }

    /**
     * Monthly lab reports
     */
    public function monthlyLabReports(Request $request)
    {
        $year = (int) ($request->input('year') ?? date('Y'));
        $month = (int) ($request->input('month') ?? date('n'));

        $this->labService->setMonthlyDates($year, $month);
        $data = $this->labService->buildReport();

        $data['year'] = $year;
        $data['month'] = $month;
        $data['month_name'] = Carbon::createFromDate($year, $month, 1)->format('F');

        if ($request->has('pdf')) {
            return $this->downloadPdf('monthly-lab-reports', $data);
        }

        return view('admin.reports.monthly-lab-reports', $data);
    }

    /**
     * Hematology lab report
     */
    public function labHematology(Request $request)
    {
        $year = (int) ($request->input('year') ?? date('Y'));
        $month = (int) ($request->input('month') ?? date('n'));

        $this->labHematologyService->setMonthlyDates($year, $month);
        $data = $this->labHematologyService->buildReport();

        $data['year'] = $year;
        $data['month'] = $month;
        $data['month_name'] = Carbon::createFromDate($year, $month, 1)->format('F');

        if ($request->has('pdf')) {
            return $this->downloadPdf('lab-hematology', $data);
        }

        return view('admin.reports.lab-hematology', $data);
    }

    /**
     * Blood transfusion lab report
     */
    public function labBloodTransfusion(Request $request)
    {
        $year = (int) ($request->input('year') ?? date('Y'));
        $month = (int) ($request->input('month') ?? date('n'));

        $this->labBloodTransfusionService->setMonthlyDates($year, $month);
        $data = $this->labBloodTransfusionService->buildReport();

        $data['year'] = $year;
        $data['month'] = $month;
        $data['month_name'] = Carbon::createFromDate($year, $month, 1)->format('F');

        if ($request->has('pdf')) {
            return $this->downloadPdf('lab-blood-transfusion', $data);
        }

        return view('admin.reports.lab-blood-transfusion', $data);
    }

    /**
     * Clinical chemistry lab report
     */
    public function labClinicalChemistry(Request $request)
    {
        $year = (int) ($request->input('year') ?? date('Y'));
        $month = (int) ($request->input('month') ?? date('n'));

        $this->labClinicalChemistryService->setMonthlyDates($year, $month);
        $data = $this->labClinicalChemistryService->buildReport();

        $data['year'] = $year;
        $data['month'] = $month;
        $data['month_name'] = Carbon::createFromDate($year, $month, 1)->format('F');

        if ($request->has('pdf')) {
            return $this->downloadPdf('lab-clinical-chemistry', $data);
        }

        return view('admin.reports.lab-clinical-chemistry', $data);
    }

    /**
     * Microbiology lab report
     */
    public function labMicrobiology(Request $request)
    {
        $year = (int) ($request->input('year') ?? date('Y'));
        $month = (int) ($request->input('month') ?? date('n'));

        $this->labMicrobiologyService->setMonthlyDates($year, $month);
        $data = $this->labMicrobiologyService->buildReport();

        $data['year'] = $year;
        $data['month'] = $month;
        $data['month_name'] = Carbon::createFromDate($year, $month, 1)->format('F');

        if ($request->has('pdf')) {
            return $this->downloadPdf('lab-microbiology', $data);
        }

        return view('admin.reports.lab-microbiology', $data);
    }

    /**
     * Serology lab report
     */
    public function labSerology(Request $request)
    {
        $year = (int) ($request->input('year') ?? date('Y'));
        $month = (int) ($request->input('month') ?? date('n'));

        $this->labSerologyService->setMonthlyDates($year, $month);
        $data = $this->labSerologyService->buildReport();

        $data['year'] = $year;
        $data['month'] = $month;
        $data['month_name'] = Carbon::createFromDate($year, $month, 1)->format('F');

        if ($request->has('pdf')) {
            return $this->downloadPdf('lab-serology', $data);
        }

        return view('admin.reports.lab-serology', $data);
    }

    /**
     * Parasitology lab report
     */
    public function labParasitology(Request $request)
    {
        $year = (int) ($request->input('year') ?? date('Y'));
        $month = (int) ($request->input('month') ?? date('n'));

        $this->labParasitologyService->setMonthlyDates($year, $month);
        $data = $this->labParasitologyService->buildReport();

        $data['year'] = $year;
        $data['month'] = $month;
        $data['month_name'] = Carbon::createFromDate($year, $month, 1)->format('F');

        if ($request->has('pdf')) {
            return $this->downloadPdf('lab-parasitology', $data);
        }

        return view('admin.reports.lab-parasitology', $data);
    }

    /**
     * Malaria vipimo (lab tests) monthly report — Fomu ya Taarifa ya Vipimo vya Malaria
     */
    public function malariaVipimo(Request $request)
    {
        $year  = (int) ($request->input('year')  ?? date('Y'));
        $month = (int) ($request->input('month') ?? date('n'));

        $this->malariaVipimoService->setMonthlyDates($year, $month);
        $data = $this->malariaVipimoService->buildReport();

        $data['year']       = $year;
        $data['month']      = $month;
        $data['month_name'] = Carbon::createFromDate($year, $month, 1)->format('F');

        if ($request->has('pdf')) {
            $html = view('admin.reports.pdfs.malaria-vipimo', $data)->render();
            $pdf  = Pdf::loadHTML($html)->setPaper('a4', 'portrait');
            return $pdf->download('malaria-vipimo-' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.pdf');
        }

        return view('admin.reports.malaria-vipimo', $data);
    }

    /**
     * Download report as PDF
     */
    protected function downloadPdf($reportType, $data)
    {
        $viewPath = "admin.reports.pdfs.{$reportType}";

        $html = view($viewPath, $data)->render();
        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'landscape');

        $fileName = $reportType . '-' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($fileName);
    }
}
