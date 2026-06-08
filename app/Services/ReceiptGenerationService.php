<?php

namespace App\Services;

use App\Models\FinancialTransaction;
use App\Models\Patient;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ReceiptGenerationService
{
    /**
     * Generate receipt for a financial transaction
     */
    public function generateReceipt(FinancialTransaction $transaction, $format = 'pdf')
    {
        // Load necessary relationships
        $transaction->load(['patient', 'creator']);
        
        $receiptData = $this->prepareReceiptData($transaction);
        
        switch ($format) {
            case 'pdf':
                return $this->generatePdfReceipt($receiptData);
            case 'html':
                return $this->generateHtmlReceipt($receiptData);
            case 'thermal':
                return $this->generateThermalReceipt($receiptData);
            default:
                return $this->generatePdfReceipt($receiptData);
        }
    }

    /**
     * Generate multiple receipts for a patient's transactions
     */
    public function generatePatientStatementReceipt(Patient $patient, $dateFrom = null, $dateTo = null)
    {
        $query = FinancialTransaction::where('patient_id', $patient->id)
            ->where('transaction_type', 'income')
            ->orderBy('transaction_date', 'desc');

        if ($dateFrom) {
            $query->where('transaction_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('transaction_date', '<=', $dateTo);
        }

        $transactions = $query->get();
        
        $statementData = [
            'patient' => $patient,
            'transactions' => $transactions,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'total_amount' => $transactions->sum('amount'),
            'total_insurance' => $transactions->sum('insurance_covered_amount'),
            'total_patient_paid' => $transactions->sum('patient_paid_amount'),
            'statement_date' => now(),
            'statement_number' => 'STMT-' . $patient->id . '-' . now()->format('YmdHis')
        ];

        return $this->generatePdfStatement($statementData);
    }

    /**
     * Prepare receipt data
     */
    protected function prepareReceiptData(FinancialTransaction $transaction)
    {
        return [
            'receipt_number' => $transaction->transaction_number,
            'transaction' => $transaction,
            'patient' => $transaction->patient,
            'receipt_date' => now(),
            'issued_by' => $transaction->creator->name ?? 'System',
            'clinic_info' => $this->getClinicInfo(),
            'payment_details' => $this->getPaymentDetails($transaction),
            'service_details' => $this->getServiceDetails($transaction)
        ];
    }

    /**
     * Generate PDF receipt
     */
    protected function generatePdfReceipt($receiptData)
    {
        $pdf = PDF::loadView('financial.receipts.standard-receipt', $receiptData);
        $pdf->setPaper('a4', 'portrait');
        
        $filename = 'receipt_' . $receiptData['receipt_number'] . '.pdf';
        
        return [
            'content' => $pdf->output(),
            'filename' => $filename,
            'content_type' => 'application/pdf'
        ];
    }

    /**
     * Generate HTML receipt
     */
    protected function generateHtmlReceipt($receiptData)
    {
        $html = View::make('financial.receipts.standard-receipt', $receiptData)->render();
        
        return [
            'content' => $html,
            'filename' => 'receipt_' . $receiptData['receipt_number'] . '.html',
            'content_type' => 'text/html'
        ];
    }

    /**
     * Generate thermal printer receipt (58mm or 80mm)
     */
    protected function generateThermalReceipt($receiptData)
    {
        $html = View::make('financial.receipts.thermal-receipt', $receiptData)->render();
        
        return [
            'content' => $html,
            'filename' => 'thermal_receipt_' . $receiptData['receipt_number'] . '.html',
            'content_type' => 'text/html'
        ];
    }

    /**
     * Generate PDF statement
     */
    protected function generatePdfStatement($statementData)
    {
        $pdf = PDF::loadView('financial.receipts.patient-statement', $statementData);
        $pdf->setPaper('a4', 'portrait');
        
        $filename = $statementData['statement_number'] . '.pdf';
        
        return [
            'content' => $pdf->output(),
            'filename' => $filename,
            'content_type' => 'application/pdf'
        ];
    }

    /**
     * Get clinic information
     */
    protected function getClinicInfo()
    {
        $f = \App\Models\Facility::current();
        return [
            'name'    => $f->name,
            'address' => $f->address,
            'phone'   => $f->phone,
            'email'   => $f->email,
            'license' => config('app.clinic_license', ''),
            'tax_id'  => config('app.clinic_tax_id', ''),
        ];
    }

    /**
     * Get payment details
     */
    protected function getPaymentDetails(FinancialTransaction $transaction)
    {
        return [
            'payment_method' => ucfirst(str_replace('_', ' ', $transaction->payment_method)),
            'payment_reference' => $transaction->payment_reference,
            'total_amount' => $transaction->amount,
            'insurance_covered' => $transaction->insurance_covered_amount ?? 0,
            'patient_paid' => $transaction->patient_paid_amount ?? $transaction->amount,
            'balance_due' => 0, // Assuming paid in full for receipts
            'payment_status' => ucfirst($transaction->status)
        ];
    }

    /**
     * Get service details based on transaction source
     */
    protected function getServiceDetails(FinancialTransaction $transaction)
    {
        $details = [
            'service_type' => ucfirst(str_replace('_', ' ', $transaction->category)),
            'service_description' => $transaction->description,
            'service_date' => $transaction->transaction_date,
            'items' => []
        ];

        // Add specific details based on source type
        switch ($transaction->source_type) {
            case 'investigation':
                if ($transaction->source) {
                    $details['items'][] = [
                        'name' => $transaction->source->medicalService->name ?? 'Laboratory Investigation',
                        'quantity' => $transaction->source->quantity ?? 1,
                        'unit_price' => $transaction->source->unit_price ?? $transaction->amount,
                        'total_price' => $transaction->amount
                    ];
                }
                break;

            case 'prescription_item':
                if ($transaction->source) {
                    $details['items'][] = [
                        'name' => $transaction->source->medication->name ?? 'Medication',
                        'quantity' => $transaction->source->quantity_dispensed ?? 1,
                        'unit_price' => $transaction->source->unit_selling_price ?? 0,
                        'total_price' => $transaction->amount
                    ];
                }
                break;

            case 'consultation':
            case 'consultation_fee':
                $details['items'][] = [
                    'name' => 'Medical Consultation',
                    'quantity' => 1,
                    'unit_price' => $transaction->amount,
                    'total_price' => $transaction->amount
                ];
                break;

            default:
                $details['items'][] = [
                    'name' => $transaction->subcategory ?? 'Service',
                    'quantity' => 1,
                    'unit_price' => $transaction->amount,
                    'total_price' => $transaction->amount
                ];
                break;
        }

        // Fallback when source-specific relation data is unavailable.
        if (empty($details['items'])) {
            $details['items'][] = [
                'name' => $transaction->description
                    ?: ($transaction->subcategory ?: ucfirst((string) $transaction->category)),
                'quantity' => 1,
                'unit_price' => $transaction->amount,
                'total_price' => $transaction->amount,
            ];
        }

        return $details;
    }

    /**
     * Save receipt to storage
     */
    public function saveReceipt($receiptContent, $filename, $patientId = null)
    {
        $path = 'receipts/' . now()->format('Y/m/d');
        
        if ($patientId) {
            $path .= '/patient_' . $patientId;
        }

        $fullPath = $path . '/' . $filename;
        
        Storage::disk('local')->put($fullPath, $receiptContent);
        
        return $fullPath;
    }

    /**
     * Email receipt to patient
     */
    public function emailReceipt(FinancialTransaction $transaction, $recipientEmail = null)
    {
        $email = $recipientEmail ?? optional($transaction->patient)->email ?? null;

        if (!$email) {
            throw new \Exception('No email address available for this patient. Please add an email address to the patient record.');
        }

        $receipt = $this->generateReceipt($transaction, 'pdf');

        // Save PDF to a temp file so it can be attached
        $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $receipt['filename'];
        file_put_contents($tmpPath, $receipt['content']);

        try {
            $patientName = optional($transaction->patient)->first_name
                ? ($transaction->patient->first_name . ' ' . $transaction->patient->last_name)
                : 'Patient';

            $subject = 'Payment Receipt – ' . $receipt['filename'];

            $bodyHtml = '<p>Dear ' . e($patientName) . ',</p>'
                . '<p>Please find your payment receipt attached to this email.</p>'
                . '<p>Receipt Number: <strong>' . e($transaction->transaction_number) . '</strong></p>'
                . '<p>Amount Paid: <strong>Tsh ' . number_format((float) $transaction->amount, 2) . '</strong></p>'
                . '<p>Thank you for choosing ' . e(config('app.name', 'our facility')) . '.</p>';

            \Illuminate\Support\Facades\Mail::to($email)
                ->send(new \App\Mail\GenericMail($subject, $bodyHtml, [$tmpPath]));
        } finally {
            // Clean up temp file regardless of success/failure
            if (file_exists($tmpPath)) {
                @unlink($tmpPath);
            }
        }

        return true;
    }

    /**
     * Generate daily receipt summary
     */
    public function generateDailyReceiptSummary($date = null)
    {
        $date = $date ?? now()->toDateString();
        
        $transactions = FinancialTransaction::whereDate('transaction_date', $date)
            ->where('transaction_type', 'income')
            ->with(['patient', 'creator'])
            ->orderBy('transaction_date')
            ->get();

        $summaryData = [
            'date' => $date,
            'total_transactions' => $transactions->count(),
            'total_amount' => $transactions->sum('amount'),
            'total_cash' => $transactions->where('payment_method', 'cash')->sum('amount'),
            'total_insurance' => $transactions->sum('insurance_covered_amount'),
            'transactions_by_category' => $transactions->groupBy('category'),
            'transactions' => $transactions
        ];

        $pdf = PDF::loadView('financial.receipts.daily-summary', $summaryData);
        $pdf->setPaper('a4', 'portrait');
        
        $filename = 'daily_summary_' . str_replace('-', '', $date) . '.pdf';
        
        return [
            'content' => $pdf->output(),
            'filename' => $filename,
            'content_type' => 'application/pdf'
        ];
    }
}
