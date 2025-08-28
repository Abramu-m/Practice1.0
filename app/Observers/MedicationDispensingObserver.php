<?php

namespace App\Observers;

use App\Models\Prescription;
use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class MedicationDispensingObserver
{
    /**
     * Handle the Prescription "updated" event.
     * Automatically create financial transaction when prescription is paid
     */
    public function updated(Prescription $prescription)
    {
        Log::info("MedicationDispensingObserver::updated called", [
            'prescription_id' => $prescription->id,
            'is_paid_dirty' => $prescription->isDirty('is_paid'),
            'current_is_paid' => $prescription->is_paid,
            'original_is_paid' => $prescription->getOriginal('is_paid'),
            'dirty_attributes' => $prescription->getDirty()
        ]);

        // Check if is_paid was just set to true (medication was paid for)
        if ($prescription->isDirty('is_paid') && 
            $prescription->is_paid === true &&
            $prescription->getOriginal('is_paid') !== true) {
            
            Log::info("Creating financial transaction for prescription payment", [
                'prescription_id' => $prescription->id
            ]);
            $this->createPrescriptionPaymentTransaction($prescription);
        }
    }

    /**
     * Handle the Prescription "created" event.
     * Create transaction when prescription is created and immediately paid
     */
    public function created(Prescription $prescription)
    {
        // If created with is_paid already set to true
        if ($prescription->is_paid === true) {
            $this->createPrescriptionPaymentTransaction($prescription);
        }
    }

    /**
     * Create financial transaction for prescription payment
     */
    protected function createPrescriptionPaymentTransaction(Prescription $prescription)
    {
        try {
            // Skip if transaction already exists
            $existingTransaction = FinancialTransaction::where('source_type', 'prescription')
                ->where('source_id', $prescription->id)
                ->where('transaction_type', 'income')
                ->first();

            if ($existingTransaction) {
                Log::info("Financial transaction already exists for prescription ID: {$prescription->id}");
                return;
            }

            // Load relationships
            $prescription->load(['patient', 'consultation']);

            // Calculate amounts - you may need to adjust based on your prescription pricing logic
            $totalAmount = $prescription->amount_paid ?? $prescription->total_amount ?? 0;
            $discountAmount = 0;
            
            if ($prescription->is_discount && $prescription->discount_percent > 0) {
                $originalAmount = $totalAmount / (1 - ($prescription->discount_percent / 100));
                $discountAmount = $originalAmount - $totalAmount;
            }

            // Get patient and visit information
            $patient = $prescription->patient ?? null;
            $consultation = $prescription->consultation ?? null;
            $patientName = $patient ? ($patient->first_name . ' ' . $patient->last_name) : 'Unknown Patient';

            // Create financial transaction
            $transactionData = [
                'transaction_date' => $prescription->paid_at ?? now(),
                'transaction_type' => 'income',
                'category' => 'medication_sales',
                'subcategory' => 'prescription_payment',
                'amount' => $totalAmount,
                'description' => "Prescription payment - {$patientName}",
                'source_type' => 'prescription',
                'source_id' => $prescription->id,
                'patient_id' => $patient->id ?? null,
                'visit_id' => $consultation->patient_visit_id ?? null,
                'payment_method' => $prescription->payment_method ?? 'cash',
                'payment_reference' => "PRESC-{$prescription->id}",
                'insurance_covered_amount' => 0, // Adjust if you have insurance logic
                'patient_paid_amount' => $totalAmount,
                'status' => 'completed',
                'created_by' => Auth::id() ?? 1,
                'approved_by' => Auth::id() ?? 1,
                'approved_at' => now(),
                'notes' => $prescription->is_discount ? "Prescription payment with {$prescription->discount_percent}% discount" : 'Auto-generated from prescription payment'
            ];

            $transaction = FinancialTransaction::create($transactionData);

            Log::info("Financial transaction created for prescription payment", [
                'prescription_id' => $prescription->id,
                'transaction_id' => $transaction->id,
                'amount' => $totalAmount
            ]);

        } catch (\Exception $e) {
            Log::error("Error creating financial transaction for prescription payment", [
                'prescription_id' => $prescription->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle prescription deletion
     */
    public function deleted(Prescription $prescription)
    {
        try {
            // Mark related financial transactions as refunded
            FinancialTransaction::where('source_type', 'prescription')
                ->where('source_id', $prescription->id)
                ->update([
                    'status' => 'refunded',
                    'notes' => 'Prescription cancelled - auto-refunded'
                ]);

            Log::info("Financial transactions refunded for deleted prescription", [
                'prescription_id' => $prescription->id
            ]);

        } catch (\Exception $e) {
            Log::error("Error handling financial transactions for deleted prescription", [
                'prescription_id' => $prescription->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}