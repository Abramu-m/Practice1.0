<?php

namespace App\Services;

use App\Models\FinancialTransaction;
use App\Models\PatientVisit;
use App\Models\Investigation;
use App\Models\Prescription;
use App\Models\GoodsReceivedNote;
use Illuminate\Support\Facades\Log;

class FinancialTransactionService
{
    /**
     * Create transaction from patient visit payment
     */
    public function createFromVisitPayment(PatientVisit $visit): ?FinancialTransaction
    {
        try {
            $totalAmount = $visit->amount_cash + $visit->amount_covered;
            
            if ($totalAmount <= 0) {
                return null;
            }

            // Check if transaction already exists for this visit
            $existingTransaction = FinancialTransaction::where('source_type', 'consultation')
                ->where('source_id', $visit->id)
                ->first();

            if ($existingTransaction) {
                Log::info("Transaction already exists for visit {$visit->id}");
                return $existingTransaction;
            }

            $transaction = FinancialTransaction::create([
                'transaction_type' => 'income',
                'category' => 'consultation',
                'subcategory' => 'consultation_fee',
                'amount' => $totalAmount,
                'description' => "Consultation fee for patient visit #{$visit->id}",
                'source_type' => 'consultation',
                'source_id' => $visit->id,
                'patient_id' => $visit->patient,
                'visit_id' => $visit->id,
                'payment_method' => $visit->amount_covered > 0 ? 'insurance' : 'cash',
                'patient_paid_amount' => $visit->amount_cash,
                'insurance_covered_amount' => $visit->amount_covered,
                'status' => 'completed'
            ]);

            Log::info("Created financial transaction {$transaction->transaction_number} for visit {$visit->id}");
            
            return $transaction;
            
        } catch (\Exception $e) {
            Log::error("Failed to create transaction for visit {$visit->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create transaction from investigation payment
     */
    public function createFromInvestigationPayment(Investigation $investigation): ?FinancialTransaction
    {
        try {
            if (!$investigation->is_paid || $investigation->total_price <= 0) {
                return null;
            }

            // Check if transaction already exists for this investigation
            $existingTransaction = FinancialTransaction::where('source_type', 'investigation')
                ->where('source_id', $investigation->id)
                ->first();

            if ($existingTransaction) {
                Log::info("Transaction already exists for investigation {$investigation->id}");
                return $existingTransaction;
            }

            $transaction = FinancialTransaction::create([
                'transaction_type' => 'income',
                'category' => 'investigation',
                'subcategory' => $investigation->medicalService->serviceCategory->name ?? 'laboratory',
                'amount' => $investigation->total_price,
                'description' => "Investigation: {$investigation->medicalService->name}",
                'source_type' => 'investigation',
                'source_id' => $investigation->id,
                'patient_id' => $investigation->patient_id,
                'visit_id' => $investigation->visit_id,
                'payment_method' => $investigation->insurance_covered_amount > 0 ? 'insurance' : 'cash',
                'patient_paid_amount' => $investigation->total_price - $investigation->insurance_covered_amount,
                'insurance_covered_amount' => $investigation->insurance_covered_amount,
                'status' => 'completed'
            ]);

            Log::info("Created financial transaction {$transaction->transaction_number} for investigation {$investigation->id}");
            
            return $transaction;
            
        } catch (\Exception $e) {
            Log::error("Failed to create transaction for investigation {$investigation->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create transaction from medication dispensing
     */
    public function createFromMedicationDispensing(Prescription $prescription): ?FinancialTransaction
    {
        try {
            if (!$prescription->is_paid || ($prescription->total_price ?? 0) <= 0) {
                return null;
            }

            // Check if transaction already exists for this prescription
            $existingTransaction = FinancialTransaction::where('source_type', 'prescription')
                ->where('source_id', $prescription->id)
                ->first();

            if ($existingTransaction) {
                Log::info("Transaction already exists for prescription {$prescription->id}");
                return $existingTransaction;
            }

            $totalAmount = $prescription->amount_paid ?? $prescription->total_amount ?? 0;

            $transaction = FinancialTransaction::create([
                'transaction_type' => 'income',
                'category' => 'medication_sales',
                'subcategory' => 'prescription_payment',
                'amount' => $totalAmount,
                'description' => "Prescription payment for patient",
                'source_type' => 'prescription',
                'source_id' => $prescription->id,
                'patient_id' => $prescription->patient_id,
                'visit_id' => $prescription->visit_id ?? null,
                'payment_method' => $prescription->payment_method ?? 'cash',
                'patient_paid_amount' => $totalAmount,
                'insurance_covered_amount' => 0,
                'status' => 'completed'
            ]);

            Log::info("Created financial transaction {$transaction->transaction_number} for prescription {$prescription->id}");
            
            return $transaction;
            
        } catch (\Exception $e) {
            Log::error("Failed to create transaction for prescription {$prescription->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create transaction from GRN payment
     */
    public function createFromGrnPayment(GoodsReceivedNote $grn): ?FinancialTransaction
    {
        try {
            if ($grn->status !== 'posted' || $grn->net_amount <= 0) {
                return null;
            }

            // Check if transaction already exists for this GRN
            $existingTransaction = FinancialTransaction::where('source_type', 'grn_purchase')
                ->where('source_id', $grn->id)
                ->first();

            if ($existingTransaction) {
                Log::info("Transaction already exists for GRN {$grn->id}");
                return $existingTransaction;
            }

            $transaction = FinancialTransaction::create([
                'transaction_type' => 'expense',
                'category' => 'grn_purchase',
                'subcategory' => 'medical_supplies',
                'amount' => $grn->net_amount,
                'description' => "GRN Purchase: {$grn->grn_number}",
                'source_type' => 'grn_purchase',
                'source_id' => $grn->id,
                'payment_method' => 'bank', // GRNs are typically paid via bank
                'payment_reference' => $grn->invoice_number,
                'patient_paid_amount' => 0,
                'insurance_covered_amount' => 0,
                'status' => 'completed'
            ]);

            Log::info("Created financial transaction {$transaction->transaction_number} for GRN {$grn->id}");
            
            return $transaction;
            
        } catch (\Exception $e) {
            Log::error("Failed to create transaction for GRN {$grn->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update existing transaction when source is modified
     */
    public function updateTransactionFromSource(string $sourceType, int $sourceId, array $newData): ?FinancialTransaction
    {
        try {
            $transaction = FinancialTransaction::where('source_type', $sourceType)
                ->where('source_id', $sourceId)
                ->first();

            if (!$transaction) {
                Log::warning("No transaction found for {$sourceType} {$sourceId}");
                return null;
            }

            $transaction->update($newData);
            
            Log::info("Updated financial transaction {$transaction->transaction_number} for {$sourceType} {$sourceId}");
            
            return $transaction;
            
        } catch (\Exception $e) {
            Log::error("Failed to update transaction for {$sourceType} {$sourceId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete transaction when source is deleted
     */
    public function deleteTransactionFromSource(string $sourceType, int $sourceId): bool
    {
        try {
            $transaction = FinancialTransaction::where('source_type', $sourceType)
                ->where('source_id', $sourceId)
                ->first();

            if (!$transaction) {
                Log::warning("No transaction found for {$sourceType} {$sourceId}");
                return false;
            }

            $transactionNumber = $transaction->transaction_number;
            $transaction->delete();
            
            Log::info("Deleted financial transaction {$transactionNumber} for {$sourceType} {$sourceId}");
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to delete transaction for {$sourceType} {$sourceId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get financial summary for a date range
     */
    public function getFinancialSummary(\DateTime $startDate, \DateTime $endDate): array
    {
        $transactions = FinancialTransaction::whereBetween('transaction_date', [$startDate, $endDate])->get();

        $summary = [
            'total_income' => 0,
            'total_expenses' => 0,
            'net_balance' => 0,
            'transaction_count' => $transactions->count(),
            'income_by_category' => [],
            'expenses_by_category' => [],
            'payment_methods' => []
        ];

        foreach ($transactions as $transaction) {
            if ($transaction->transaction_type === 'income') {
                $summary['total_income'] += $transaction->amount;
                $summary['income_by_category'][$transaction->category] = 
                    ($summary['income_by_category'][$transaction->category] ?? 0) + $transaction->amount;
            } else {
                $summary['total_expenses'] += $transaction->amount;
                $summary['expenses_by_category'][$transaction->category] = 
                    ($summary['expenses_by_category'][$transaction->category] ?? 0) + $transaction->amount;
            }

            $summary['payment_methods'][$transaction->payment_method] = 
                ($summary['payment_methods'][$transaction->payment_method] ?? 0) + $transaction->amount;
        }

        $summary['net_balance'] = $summary['total_income'] - $summary['total_expenses'];

        return $summary;
    }

    /**
     * Process investigation payment and handle business logic
     */
    public function processInvestigationPayment(Investigation $investigation, FinancialTransaction $transaction)
    {
        try {
            Log::info("Processing investigation payment", [
                'investigation_id' => $investigation->id,
                'transaction_id' => $transaction->id,
                'amount' => $transaction->amount
            ]);

            // Update investigation payment status if needed
            if (!$investigation->is_paid) {
                $investigation->update(['is_paid' => true]);
            }

            return [
                'success' => true,
                'message' => 'Investigation payment processed successfully'
            ];

        } catch (\Exception $e) {
            Log::error("Error processing investigation payment", [
                'investigation_id' => $investigation->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error processing investigation payment: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create transaction from cash sale payment
     */
    public function createFromCashSalePayment(\App\Models\MedicationCashSale $cashSale, string $paymentMethod): ?FinancialTransaction
    {
        try {
            if ($cashSale->final_amount <= 0) {
                return null;
            }

            // Check if transaction already exists for this cash sale
            $existingTransaction = FinancialTransaction::where('source_type', 'medication_cash_sale')
                ->where('source_id', $cashSale->id)
                ->first();

            if ($existingTransaction) {
                Log::info("Transaction already exists for cash sale {$cashSale->id}");
                return $existingTransaction;
            }

            $transaction = FinancialTransaction::create([
                'transaction_type' => 'income',
                'category' => 'medication',
                'subcategory' => 'cash_sales',
                'amount' => $cashSale->final_amount,
                'description' => "Cash sale payment - {$cashSale->sale_number}",
                'source_type' => 'medication_cash_sale',
                'source_id' => $cashSale->id,
                'payment_method' => $paymentMethod,
                'patient_paid_amount' => $cashSale->final_amount,
                'insurance_covered_amount' => 0,
                'status' => 'completed'
            ]);

            Log::info("Created financial transaction {$transaction->transaction_number} for cash sale {$cashSale->id}");
            
            return $transaction;
            
        } catch (\Exception $e) {
            Log::error("Failed to create transaction for cash sale {$cashSale->id}: " . $e->getMessage());
            return null;
        }
    }
}
