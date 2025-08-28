<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FinancialTransaction;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;

class FinancialTestDataSeeder extends Seeder
{
    public function run()
    {
        // Get existing patients or skip if none exist
        $patients = Patient::limit(3)->get();
        
        if ($patients->isEmpty()) {
            $this->command->info('No patients found. Skipping test data creation.');
            $this->command->info('Please create some patients first, or run this seeder after adding patients.');
            return;
        }

        // Create test financial transactions
        $categories = ['consultation', 'investigation', 'medication'];
        $paymentMethods = ['cash', 'insurance', 'bank', 'mobile_money'];
        $sourceTypes = ['consultation', 'investigation', 'medication'];
        
        foreach ($patients as $patient) {
            for ($j = 0; $j < 5; $j++) {
                $amount = rand(5000, 50000) / 100; // $50.00 to $500.00
                $insuranceCovered = rand(0, 1) ? rand(1000, 10000) / 100 : 0;
                $patientPaid = $amount - $insuranceCovered;
                
                FinancialTransaction::create([
                    'transaction_number' => 'TXN-' . time() . '-' . $patient->id . '-' . $j,
                    'transaction_date' => Carbon::now()->subDays(rand(0, 30)),
                    'transaction_type' => 'income',
                    'category' => $categories[array_rand($categories)],
                    'amount' => $amount,
                    'description' => 'Test transaction for ' . $patient->first_name,
                    'source_type' => $sourceTypes[array_rand($sourceTypes)],
                    'source_id' => rand(1, 100),
                    'patient_id' => $patient->id,
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'payment_reference' => 'REF-' . time() . '-' . rand(100, 999),
                    'insurance_covered_amount' => $insuranceCovered,
                    'patient_paid_amount' => $patientPaid,
                    'status' => 'completed',
                    'created_by' => User::first()->id ?? 1,
                    'notes' => 'Test data for receipt system testing'
                ]);
            }
        }

        $this->command->info('Test financial data created successfully!');
        $this->command->info('Used ' . count($patients) . ' existing patients');
        $this->command->info('Created ' . (count($patients) * 5) . ' test transactions');
    }
}
