<?php

namespace App\Observers;

use App\Models\Investigation;
use App\Models\FinancialTransaction;
use App\Services\FinancialTransactionService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class InvestigationFinancialObserver
{
    protected $financialService;

    public function __construct(FinancialTransactionService $financialService)
    {
        $this->financialService = $financialService;
    }

    /**
     * Handle the Investigation "updated" event.
     * Automatically create financial transaction when investigation is paid
     */
    public function updated(Investigation $investigation)
    {
        Log::info("InvestigationFinancialObserver::updated called", [
            'investigation_id' => $investigation->id,
            'is_paid_dirty' => $investigation->isDirty('is_paid'),
            'current_is_paid' => $investigation->is_paid,
            'original_is_paid' => $investigation->getOriginal('is_paid'),
            'dirty_attributes' => $investigation->getDirty()
        ]);

        // Check if is_paid changed to true and no financial transaction exists yet
        if ($investigation->isDirty('is_paid') && 
            $investigation->is_paid === true &&
            $investigation->getOriginal('is_paid') !== true) {
            
            Log::info("Creating financial transaction for investigation payment", [
                'investigation_id' => $investigation->id
            ]);
            $this->createInvestigationPaymentTransaction($investigation);
        }

        // Handle status change to 'resulted' for completion tracking
        if ($investigation->isDirty('status') && 
            $investigation->status === Investigation::STATUS_RESULTED &&
            $investigation->getOriginal('status') !== Investigation::STATUS_RESULTED) {
            
            $this->markInvestigationCompleted($investigation);
        }
    }

    /**
     * Create financial transaction for investigation payment
     */
    protected function createInvestigationPaymentTransaction(Investigation $investigation)
    {
        try {
            // Check if transaction already exists to prevent duplicates
            $existingTransaction = FinancialTransaction::where('source_type', 'investigation')
                ->where('source_id', $investigation->id)
                ->where('transaction_type', 'income')
                ->first();

            if ($existingTransaction) {
                Log::info("Financial transaction already exists for investigation ID: {$investigation->id}");
                return;
            }

            // Calculate amounts
            $insuranceCovered = $investigation->insurance_covered_amount ?? 0;
            $patientPaid = $investigation->cash_amount ?? 0;
            $totalAmount = $patientPaid + $insuranceCovered;

            // Create financial transaction
            $serviceName = $investigation->medicalService->name ?? 'Laboratory Investigation';
            $patientName = ($investigation->patient->first_name ?? '') . ' ' . ($investigation->patient->last_name ?? '');
            $visitId = $investigation->consultation ? $investigation->consultation->patient_visit_id : null;
            
            $transactionData = [
                'transaction_date' => now(),
                'transaction_type' => 'income',
                'category' => 'investigation_services',
                'subcategory' => $serviceName,
                'amount' => $totalAmount,
                'description' => "Payment for {$serviceName} - {$patientName}",
                'source_type' => 'investigation',
                'source_id' => $investigation->id,
                'patient_id' => $investigation->patient_id,
                'visit_id' => $visitId,
                'payment_method' => $this->determinePaymentMethod($investigation),
                'payment_reference' => "INV-{$investigation->id}",
                'insurance_covered_amount' => $insuranceCovered,
                'patient_paid_amount' => $patientPaid,
                'status' => 'completed',
                'created_by' => Auth::id() ?? 1,
                'approved_by' => Auth::id() ?? 1,
                'approved_at' => now(),
                'notes' => 'Auto-generated from investigation payment'
            ];

            $transaction = FinancialTransaction::create($transactionData);

            // Log successful transaction creation
            Log::info("Financial transaction created for investigation payment", [
                'investigation_id' => $investigation->id,
                'transaction_id' => $transaction->id,
                'amount' => $totalAmount
            ]);

        } catch (\Exception $e) {
            Log::error("Error creating financial transaction for investigation payment", [
                'investigation_id' => $investigation->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Mark investigation as completed in financial records
     */
    protected function markInvestigationCompleted(Investigation $investigation)
    {
        try {
            // Update the related financial transaction to mark service as delivered
            FinancialTransaction::where('source_type', 'investigation')
                ->where('source_id', $investigation->id)
                ->update([
                    'notes' => 'Investigation completed and results delivered'
                ]);

            Log::info("Investigation marked as completed in financial records", [
                'investigation_id' => $investigation->id
            ]);

        } catch (\Exception $e) {
            Log::error("Error updating financial transaction for completed investigation", [
                'investigation_id' => $investigation->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Determine payment method based on investigation data
     */
    protected function determinePaymentMethod(Investigation $investigation)
    {
        // Logic to determine payment method
        // This could be enhanced based on your business requirements
        
        if ($investigation->insurance_covered_amount > 0) {
            return 'insurance_cash'; // Mixed payment
        }

        // Default to cash - you can enhance this logic
        return 'cash';
    }

    /**
     * Handle investigation deletion
     */
    public function deleted(Investigation $investigation)
    {
        try {
            // When investigation is deleted, mark related financial transactions as refunded
            FinancialTransaction::where('source_type', 'investigation')
                ->where('source_id', $investigation->id)
                ->update([
                    'status' => 'refunded',
                    'notes' => 'Investigation cancelled - auto-refunded'
                ]);

            Log::info("Financial transactions refunded for deleted investigation", [
                'investigation_id' => $investigation->id
            ]);

        } catch (\Exception $e) {
            Log::error("Error handling financial transactions for deleted investigation", [
                'investigation_id' => $investigation->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
