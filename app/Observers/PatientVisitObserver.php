<?php

namespace App\Observers;

use App\Models\PatientVisit;
use App\Services\FinancialTransactionService;

class PatientVisitObserver
{
    protected $financialService;

    public function __construct(FinancialTransactionService $financialService)
    {
        $this->financialService = $financialService;
    }

    /**
     * Handle the PatientVisit "created" event.
     */
    public function created(PatientVisit $patientVisit): void
    {
        // Create financial transaction if payment amounts are set
        if ($patientVisit->amount_cash > 0 || $patientVisit->amount_covered > 0) {
            $this->financialService->createFromVisitPayment($patientVisit);
        }
    }

    /**
     * Handle the PatientVisit "updated" event.
     */
    public function updated(PatientVisit $patientVisit): void
    {
        // Check if payment amounts were changed
        if ($patientVisit->wasChanged(['amount_cash', 'amount_covered'])) {
            $totalAmount = $patientVisit->amount_cash + $patientVisit->amount_covered;
            
            if ($totalAmount > 0) {
                // Create or update transaction
                $this->financialService->createFromVisitPayment($patientVisit);
            } else {
                // Delete existing transaction if amounts are zeroed
                $this->financialService->deleteTransactionFromSource('consultation', $patientVisit->id);
            }
        }
    }

    /**
     * Handle the PatientVisit "deleted" event.
     */
    public function deleted(PatientVisit $patientVisit): void
    {
        // Delete associated financial transaction
        $this->financialService->deleteTransactionFromSource('consultation', $patientVisit->id);
    }
}
