<?php

namespace Database\Seeders;

use App\Models\MedicalServicePricing;
use App\Models\MedicalService;
use App\Models\PatientCategory;
use Illuminate\Database\Seeder;

class MedicalServicePricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some sample medical services and patient categories
        $services = MedicalService::take(10)->get();
        $categories = PatientCategory::all();

        foreach ($services as $service) {
            foreach ($categories as $category) {
                // Create pricing with different rates for different categories
                $basePrice = rand(10000, 100000); // Base price in TSh
                
                // Adjust price based on category
                $price = match($category->description) {
                    'Cash' => $basePrice,
                    'Insurance' => $basePrice * 0.8, // 20% discount for insurance
                    'NHIF' => $basePrice * 0.7, // 30% discount for NHIF
                    default => $basePrice
                };

                MedicalServicePricing::create([
                    'medical_service_id' => $service->id,
                    'patient_category_id' => $category->id,
                    'selling_price' => $price,
                    'markup_percentage' => rand(10, 50),
                    'discount_percentage' => $category->description === 'Cash' ? 0 : rand(10, 30),
                    'is_active' => true,
                    'effective_from' => now()->subDays(rand(1, 30)),
                    'effective_to' => rand(0, 1) ? now()->addDays(rand(30, 365)) : null,
                    'notes' => "Sample pricing for {$service->name} - {$category->description} category"
                ]);
            }
        }
    }
}
