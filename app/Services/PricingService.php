<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\MedicalService;
use App\Models\Medication;

class PricingService
{
    /**
     * Universal resolver for ANY billable item
     */
    public function resolve($item, int $patientCategoryId): array
    {
        $sellingPrice = $item->selling_price;

        //Check if item is a medical service or medication
        if ($item instanceof MedicalService) {
            $item_name = $item->name;
        } elseif ($item instanceof Medication) {
            $item_name = $item->generic_name;
        }

        // STEP 1: get mapping record
        $map = $item->pricingMap()
            ->where('patient_category_id', $patientCategoryId)
            ->first();

        if (!$map) {
            return [
                'item_code'                => null,
                'item_name'                => $item_name,
                'cash_amount'              => $sellingPrice,
                'selling_price'            => $sellingPrice,
                'insurance_covered_amount' => 0,
            ];
        }

        // STEP 2: get insurance item code
        $itemCode = $map->insurance_item_code;

        // STEP 3: get table name from PatientCategory
        $tariffsTable = $map->patientCategory->tariffs_table ?? null;

        // Guard: no tariffs table configured or no item code — fall back to selling price
        if (empty($tariffsTable) || empty($itemCode)) {
            return [
                'item_code'                => $itemCode ?? null,
                'item_name'                => $item_name,
                'cash_amount'              => $sellingPrice,
                'selling_price'            => $sellingPrice,
                'insurance_covered_amount' => 0,
            ];
        }

        // STEP 4: fetch unit_price and item_name from the tariffs table
        $tariffRecord = DB::table($tariffsTable)
            ->where('item_code', $itemCode)
            ->first();

        // Guard: item not found in tariffs table
        if (!$tariffRecord) {
            return [
                'item_code'                => $itemCode,
                'item_name'                => $item_name,
                'cash_amount'              => $sellingPrice,
                'selling_price'            => $sellingPrice,
                'insurance_covered_amount' => 0,
            ];
        }

        $insurancePrice = $tariffRecord->unit_price;
        $itemName = $tariffRecord->item_name;

        // STEP 4: calculate split, ensuring we never return negative cash amounts
        $cash = max(0, $sellingPrice - $insurancePrice);

        // If the patient category absorbs any excess over the insurance tariff,
        // drop the co-payment and charge the insurance-covered amount only.
        if ($cash > 0 && $map->patientCategory->copay_policy === 'insurance_only') {
            $cash = 0;
        }

        return [
            'item_name' => $itemName,
            'item_code' => $itemCode,
            'cash_amount' => max(0, $cash),
            'insurance_covered_amount' => $insurancePrice,
            'selling_price' => $sellingPrice,
        ];
    }
}