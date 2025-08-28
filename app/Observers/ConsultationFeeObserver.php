<?php

namespace App\Observers;

use App\Models\ConsultationFee;
use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ConsultationFeeObserver
{
    /**
     * Handle the ConsultationFee "created" event.
     * Automatically create financial transaction when consultation fee is recorded
     */
    public function created(ConsultationFee $consultationFee)
    {
        $this->createConsultationFeeTransaction($consultationFee);
    }

    /**
     * Handle the ConsultationFee "updated" event.
     * Update financial transaction if amounts change
     */
    public function updated(ConsultationFee $consultationFee)
    {
        // If amount changed, update the related financial transaction
        if ($consultationFee->isDirty('amount') || $consultationFee->isDirty('paid_amount')) {
            $this->updateConsultationFeeTransaction($consultationFee);
        }
    }

    /**
     * Create financial transaction for consultation fee
     */
    protected function createConsultationFeeTransaction(ConsultationFee $consultationFee)
    {
        try {
            // Skip if transaction already exists
            $existingTransaction = FinancialTransaction::where('source_type', 'consultation_fee')
                ->where('source_id', $consultationFee->id)
                ->where('transaction_type', 'income')
                ->first();

            if ($existingTransaction) {
                Log::info("Financial transaction already exists for consultation fee ID: {$consultationFee->id}");
                return;
            }

            // Load relationships
            $consultationFee->load(['consultation.patient', 'consultation.doctor']);

            // Get consultation and patient info
            $consultation = $consultationFee->consultation;
            $patient = $consultation->patient ?? null;
            $doctor = $consultation->doctor ?? null;
            
            $patientName = $patient ? ($patient->first_name . ' ' . $patient->last_name) : 'Unknown Patient';
            $doctorName = $doctor ? ($doctor->first_name . ' ' . $doctor->last_name) : 'Unknown Doctor';

            // Calculate amounts
            $totalAmount = $consultationFee->amount ?? 0;
            $paidAmount = $consultationFee->paid_amount ?? $totalAmount;
            $insuranceCovered = $consultationFee->insurance_amount ?? 0;

            // Create financial transaction
            $transactionData = [
                'transaction_date' => $consultationFee->created_at ?? now(),
                'transaction_type' => 'income',
                'category' => 'consultation_fees',
                'subcategory' => 'medical_consultation',
                'amount' => $paidAmount, // Record only the paid amount
                'description' => "Consultation fee - Dr. {$doctorName} - {$patientName}",
                'source_type' => 'consultation_fee',
                'source_id' => $consultationFee->id,
                'patient_id' => $patient->id ?? null,
                'visit_id' => $consultation->patient_visit_id ?? null,
                'payment_method' => $this->determinePaymentMethod($consultationFee),
                'payment_reference' => "CONS-{$consultationFee->consultation_id}",
                'insurance_covered_amount' => $insuranceCovered,
                'patient_paid_amount' => $paidAmount - $insuranceCovered,
                'status' => $this->determineTransactionStatus($consultationFee),
                'created_by' => Auth::id() ?? 1,
                'approved_by' => Auth::id() ?? 1,
                'approved_at' => now(),
                'notes' => 'Auto-generated from consultation fee'
            ];

            $transaction = FinancialTransaction::create($transactionData);

            Log::info("Financial transaction created for consultation fee", [
                'consultation_fee_id' => $consultationFee->id,
                'transaction_id' => $transaction->id,
                'amount' => $paidAmount
            ]);

        } catch (\Exception $e) {
            Log::error("Error creating financial transaction for consultation fee", [
                'consultation_fee_id' => $consultationFee->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update existing financial transaction for consultation fee
     */
    protected function updateConsultationFeeTransaction(ConsultationFee $consultationFee)
    {
        try {
            $transaction = FinancialTransaction::where('source_type', 'consultation_fee')
                ->where('source_id', $consultationFee->id)
                ->first();

            if (!$transaction) {
                // If no transaction exists, create new one
                $this->createConsultationFeeTransaction($consultationFee);
                return;
            }

            // Update transaction amounts
            $paidAmount = $consultationFee->paid_amount ?? $consultationFee->amount ?? 0;
            $insuranceCovered = $consultationFee->insurance_amount ?? 0;

            $transaction->update([
                'amount' => $paidAmount,
                'insurance_covered_amount' => $insuranceCovered,
                'patient_paid_amount' => $paidAmount - $insuranceCovered,
                'status' => $this->determineTransactionStatus($consultationFee),
                'notes' => 'Updated from consultation fee changes'
            ]);

            Log::info("Financial transaction updated for consultation fee", [
                'consultation_fee_id' => $consultationFee->id,
                'transaction_id' => $transaction->id,
                'new_amount' => $paidAmount
            ]);

        } catch (\Exception $e) {
            Log::error("Error updating financial transaction for consultation fee", [
                'consultation_fee_id' => $consultationFee->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Determine payment method based on consultation fee data
     */
    protected function determinePaymentMethod(ConsultationFee $consultationFee)
    {
        if ($consultationFee->insurance_amount > 0) {
            return 'insurance_cash'; // Mixed payment
        }

        return 'cash'; // Default to cash
    }

    /**
     * Determine transaction status based on consultation fee status
     */
    protected function determineTransactionStatus(ConsultationFee $consultationFee)
    {
        $paidAmount = $consultationFee->paid_amount ?? 0;
        $totalAmount = $consultationFee->amount ?? 0;

        if ($paidAmount >= $totalAmount) {
            return 'completed';
        } elseif ($paidAmount > 0) {
            return 'partial';
        } else {
            return 'pending';
        }
    }

    /**
     * Handle consultation fee deletion
     */
    public function deleted(ConsultationFee $consultationFee)
    {
        try {
            // Mark related financial transactions as refunded
            FinancialTransaction::where('source_type', 'consultation_fee')
                ->where('source_id', $consultationFee->id)
                ->update([
                    'status' => 'refunded',
                    'notes' => 'Consultation fee cancelled - auto-refunded'
                ]);

            Log::info("Financial transactions refunded for deleted consultation fee", [
                'consultation_fee_id' => $consultationFee->id
            ]);

        } catch (\Exception $e) {
            Log::error("Error handling financial transactions for deleted consultation fee", [
                'consultation_fee_id' => $consultationFee->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
