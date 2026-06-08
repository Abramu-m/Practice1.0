<?php

namespace App\Services;

use App\Models\Investigation;
use App\Models\Prescription;
use App\Models\MedicationCashSaleItem;
use App\Models\StoreLocationStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ConsumptionTrackingService
{
    /**
     * Read-only service for checking batches used and quantities
     * No recording/consumption functionality - only data retrieval
     */

    /**
     * Get batches used from investigations
     */
    public function getInvestigationBatchesUsed($investigationId)
    {
        try {
            $investigation = Investigation::find($investigationId);
            
            if (!$investigation) {
                return [
                    'success' => false,
                    'message' => 'Investigation not found',
                    'batches_used' => []
                ];
            }

            return [
                'success' => true,
                'investigation_id' => $investigationId,
                'batches_used' => $investigation->batches_used ?? [],
                'total_items' => count($investigation->batches_used ?? [])
            ];

        } catch (Exception $e) {
            Log::error("Error retrieving investigation batches: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error retrieving batches: ' . $e->getMessage(),
                'batches_used' => []
            ];
        }
    }

    /**
     * Get batches used from prescriptions
     */
    public function getPrescriptionBatchesUsed($prescriptionId)
    {
        try {
            $prescription = Prescription::find($prescriptionId);
            
            if (!$prescription) {
                return [
                    'success' => false,
                    'message' => 'Prescription not found',
                    'batches_used' => []
                ];
            }

            return [
                'success' => true,
                'prescription_id' => $prescriptionId,
                'batches_used' => $prescription->batches_used ?? [],
                'quantity_dispensed' => $prescription->quantity_dispensed ?? 0,
                'total_batches' => count($prescription->batches_used ?? []),
                'dispensed_by' => $prescription->dispensed_by,
                'dispensed_at' => $prescription->dispensed_at
            ];

        } catch (Exception $e) {
            Log::error("Error retrieving prescription batches: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error retrieving batches: ' . $e->getMessage(),
                'batches_used' => []
            ];
        }
    }

    /**
     * Get batches used from cash sales
     */
    public function getCashSaleBatchesUsed($cashSaleItemId)
    {
        try {
            $cashSaleItem = MedicationCashSaleItem::find($cashSaleItemId);
            
            if (!$cashSaleItem) {
                return [
                    'success' => false,
                    'message' => 'Cash sale item not found',
                    'batches_used' => []
                ];
            }

            return [
                'success' => true,
                'cash_sale_item_id' => $cashSaleItemId,
                'batches_used' => $cashSaleItem->batches_used ?? [],
                'quantity_dispensed' => $cashSaleItem->quantity_dispensed ?? 0,
                'total_batches' => count($cashSaleItem->batches_used ?? []),
                'dispensed_by' => $cashSaleItem->dispensed_by,
                'dispensed_at' => $cashSaleItem->dispensed_at
            ];

        } catch (Exception $e) {
            Log::error("Error retrieving cash sale batches: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error retrieving batches: ' . $e->getMessage(),
                'batches_used' => []
            ];
        }
    }

    /**
     * Get consumption summary for multiple investigations
     */
    public function getInvestigationsBatchSummary($investigationIds)
    {
        try {
            $investigations = Investigation::whereIn('id', $investigationIds)
                ->whereNotNull('batches_used')
                ->get();

            $summary = [];
            $totalQuantityUsed = 0;
            $allBatchesUsed = [];

            foreach ($investigations as $investigation) {
                $batchesUsed = $investigation->batches_used ?? [];
                
                foreach ($batchesUsed as $batch) {
                    $totalQuantityUsed += $batch['quantity_used'] ?? 0;
                    $allBatchesUsed[] = [
                        'investigation_id' => $investigation->id,
                        'medication_id' => $batch['medication_id'] ?? null,
                        'medication_name' => $batch['medication_name'] ?? 'Unknown',
                        'batch' => $batch['batch'] ?? null,
                        'expiry' => $batch['expiry'] ?? null,
                        'quantity_used' => $batch['quantity_used'] ?? 0,
                        'location_id' => $batch['location_id'] ?? null,
                        'location_name' => $batch['location_name'] ?? null,
                        'consumed_at' => $batch['consumed_at'] ?? null
                    ];
                }

                $summary[] = [
                    'investigation_id' => $investigation->id,
                    'batches_count' => count($batchesUsed),
                    'total_quantity' => array_sum(array_column($batchesUsed, 'quantity_used'))
                ];
            }

            return [
                'success' => true,
                'investigations_processed' => count($investigations),
                'total_quantity_used' => $totalQuantityUsed,
                'total_batches' => count($allBatchesUsed),
                'summary_by_investigation' => $summary,
                'all_batches_used' => $allBatchesUsed
            ];

        } catch (Exception $e) {
            Log::error("Error retrieving investigations batch summary: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error retrieving summary: ' . $e->getMessage(),
                'investigations_processed' => 0,
                'all_batches_used' => []
            ];
        }
    }

    /**
     * Get consumption summary for multiple prescriptions
     */
    public function getPrescriptionsBatchSummary($prescriptionIds)
    {
        try {
            $prescriptions = Prescription::whereIn('id', $prescriptionIds)
                ->whereNotNull('batches_used')
                ->get();

            $summary = [];
            $totalQuantityDispensed = 0;
            $allBatchesUsed = [];

            foreach ($prescriptions as $prescription) {
                $batchesUsed = $prescription->batches_used ?? [];
                
                foreach ($batchesUsed as $batch) {
                    $totalQuantityDispensed += $batch['quantity'] ?? 0;
                    $allBatchesUsed[] = [
                        'prescription_id' => $prescription->id,
                        'batch_number' => $batch['batch_number'] ?? null,
                        'quantity' => $batch['quantity'] ?? 0,
                        'expiry_date' => $batch['expiry_date'] ?? null
                    ];
                }

                $summary[] = [
                    'prescription_id' => $prescription->id,
                    'batches_count' => count($batchesUsed),
                    'total_quantity' => $prescription->quantity_dispensed ?? 0
                ];
            }

            return [
                'success' => true,
                'prescriptions_processed' => count($prescriptions),
                'total_quantity_dispensed' => $totalQuantityDispensed,
                'total_batches' => count($allBatchesUsed),
                'summary_by_prescription' => $summary,
                'all_batches_used' => $allBatchesUsed
            ];

        } catch (Exception $e) {
            Log::error("Error retrieving prescriptions batch summary: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error retrieving summary: ' . $e->getMessage(),
                'prescriptions_processed' => 0,
                'all_batches_used' => []
            ];
        }
    }

    /**
     * Get consumption statistics for a period (read-only)
     */
    public function getConsumptionStats($startDate, $endDate, $departmentId = null)
    {
        try {
            // Get investigations with batches used in the period
            $investigationsQuery = Investigation::whereNotNull('batches_used');

            // Get prescriptions dispensed in the period
            $prescriptions = Prescription::whereBetween('dispensed_at', [$startDate, $endDate])
                ->where('status', 'dispensed')
                ->whereNotNull('batches_used');

            // Get cash sales in the period
            $cashSales = MedicationCashSaleItem::whereBetween('dispensed_at', [$startDate, $endDate])
                ->where('status', 'dispensed')
                ->whereNotNull('batches_used');

            if ($departmentId) {
                $investigationsQuery->where('department_id', $departmentId);
            }

            $investigations = $investigationsQuery->get();

            // Calculate totals from batch data
            $investigationTotalQuantity = 0;
            $prescriptionTotalQuantity = 0;
            $cashSaleTotalQuantity = 0;

            foreach ($investigations as $investigation) {
                foreach ($investigation->batches_used ?? [] as $batch) {
                    $investigationTotalQuantity += $batch['quantity_used'] ?? 0;
                }
            }

            foreach ($prescriptions->get() as $prescription) {
                $prescriptionTotalQuantity += $prescription->quantity_dispensed ?? 0;
            }

            foreach ($cashSales->get() as $cashSale) {
                $cashSaleTotalQuantity += $cashSale->quantity_dispensed ?? 0;
            }

            return [
                'success' => true,
                'period' => ['start' => $startDate, 'end' => $endDate],
                'investigations_with_batches' => $investigations->count(),
                'prescriptions_dispensed' => $prescriptions->count(),
                'cash_sales_dispensed' => $cashSales->count(),
                'total_investigation_quantity' => $investigationTotalQuantity,
                'total_prescription_quantity' => $prescriptionTotalQuantity,
                'total_cash_sale_quantity' => $cashSaleTotalQuantity,
                'grand_total_quantity' => $investigationTotalQuantity + $prescriptionTotalQuantity + $cashSaleTotalQuantity
            ];

        } catch (Exception $e) {
            Log::error('Error in getConsumptionStats: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error retrieving consumption stats: ' . $e->getMessage(),
                'investigations_with_batches' => 0,
                'prescriptions_dispensed' => 0,
                'cash_sales_dispensed' => 0,
                'total_investigation_quantity' => 0,
                'total_prescription_quantity' => 0,
                'total_cash_sale_quantity' => 0,
                'grand_total_quantity' => 0
            ];
        }
    }

    /**
     * Get batch usage history for a specific medication
     */
    public function getMedicationBatchHistory($medicationId, $startDate = null, $endDate = null)
    {
        try {
            $batchHistory = [];

            // From investigations
            $investigationsQuery = Investigation::whereNotNull('batches_used');
            
            if ($startDate && $endDate) {
                $investigationsQuery->whereBetween('created_at', [$startDate, $endDate]);
            }

            foreach ($investigationsQuery->get() as $investigation) {
                foreach ($investigation->batches_used ?? [] as $batch) {
                    if (($batch['medication_id'] ?? null) == $medicationId) {
                        $batchHistory[] = [
                            'source_type' => 'investigation',
                            'source_id' => $investigation->id,
                            'medication_id' => $medicationId,
                            'batch' => $batch['batch'] ?? null,
                            'expiry' => $batch['expiry'] ?? null,
                            'quantity_used' => $batch['quantity_used'] ?? 0,
                            'location_id' => $batch['location_id'] ?? null,
                            'location_name' => $batch['location_name'] ?? null,
                            'consumed_at' => $batch['consumed_at'] ?? $investigation->created_at
                        ];
                    }
                }
            }

            // From prescriptions
            $prescriptionsQuery = Prescription::where('medication_id', $medicationId)
                ->whereNotNull('batches_used')
                ->where('status', 'dispensed');
            
            if ($startDate && $endDate) {
                $prescriptionsQuery->whereBetween('dispensed_at', [$startDate, $endDate]);
            }

            foreach ($prescriptionsQuery->get() as $prescription) {
                foreach ($prescription->batches_used ?? [] as $batch) {
                    $batchHistory[] = [
                        'source_type' => 'prescription',
                        'source_id' => $prescription->id,
                        'medication_id' => $medicationId,
                        'batch_number' => $batch['batch_number'] ?? null,
                        'quantity' => $batch['quantity'] ?? 0,
                        'expiry_date' => $batch['expiry_date'] ?? null,
                        'dispensed_at' => $prescription->dispensed_at
                    ];
                }
            }

            // From cash sales
            $cashSalesQuery = MedicationCashSaleItem::where('medication_id', $medicationId)
                ->whereNotNull('batches_used')
                ->where('status', 'dispensed');
            
            if ($startDate && $endDate) {
                $cashSalesQuery->whereBetween('dispensed_at', [$startDate, $endDate]);
            }

            foreach ($cashSalesQuery->get() as $cashSale) {
                foreach ($cashSale->batches_used ?? [] as $batch) {
                    $batchHistory[] = [
                        'source_type' => 'cash_sale',
                        'source_id' => $cashSale->id,
                        'medication_id' => $medicationId,
                        'batch_number' => $batch['batch_number'] ?? null,
                        'quantity' => $batch['quantity'] ?? 0,
                        'expiry_date' => $batch['expiry_date'] ?? null,
                        'dispensed_at' => $cashSale->dispensed_at
                    ];
                }
            }

            // Sort by date (newest first)
            usort($batchHistory, function($a, $b) {
                $dateA = $a['consumed_at'] ?? $a['dispensed_at'] ?? '1970-01-01';
                $dateB = $b['consumed_at'] ?? $b['dispensed_at'] ?? '1970-01-01';
                return strtotime($dateB) - strtotime($dateA);
            });

            return [
                'success' => true,
                'medication_id' => $medicationId,
                'total_records' => count($batchHistory),
                'batch_history' => $batchHistory
            ];

        } catch (Exception $e) {
            Log::error("Error retrieving medication batch history: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error retrieving batch history: ' . $e->getMessage(),
                'medication_id' => $medicationId,
                'batch_history' => []
            ];
        }
    }

    /**
     * Get top consumed medications for a period (read-only)
     */
    public function getTopConsumedMedications($startDate, $endDate, $departmentId = null, $limit = 10)
    {
        try {
            $medicationConsumption = [];

            // From investigations
            $investigationsQuery = Investigation::whereNotNull('batches_used');
            
            if ($startDate && $endDate) {
                $investigationsQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
            
            if ($departmentId) {
                $investigationsQuery->where('department_id', $departmentId);
            }

            foreach ($investigationsQuery->get() as $investigation) {
                foreach ($investigation->batches_used ?? [] as $batch) {
                    $medicationId = $batch['medication_id'] ?? null;
                    if ($medicationId) {
                        if (!isset($medicationConsumption[$medicationId])) {
                            $medicationConsumption[$medicationId] = [
                                'medication_id' => $medicationId,
                                'medication_name' => $batch['medication_name'] ?? 'Unknown',
                                'total_quantity' => 0
                            ];
                        }
                        $medicationConsumption[$medicationId]['total_quantity'] += $batch['quantity_used'] ?? 0;
                    }
                }
            }

            // From prescriptions
            $prescriptionsQuery = Prescription::whereNotNull('batches_used')
                ->where('status', 'dispensed');
            
            if ($startDate && $endDate) {
                $prescriptionsQuery->whereBetween('dispensed_at', [$startDate, $endDate]);
            }

            foreach ($prescriptionsQuery->get() as $prescription) {
                $medicationId = $prescription->medication_id;
                if ($medicationId) {
                    if (!isset($medicationConsumption[$medicationId])) {
                        $medicationConsumption[$medicationId] = [
                            'medication_id' => $medicationId,
                            'medication_name' => $prescription->medication->name ?? 'Unknown',
                            'total_quantity' => 0
                        ];
                    }
                    $medicationConsumption[$medicationId]['total_quantity'] += $prescription->quantity_dispensed ?? 0;
                }
            }

            // From cash sales
            $cashSalesQuery = MedicationCashSaleItem::whereNotNull('batches_used')
                ->where('status', 'dispensed');
            
            if ($startDate && $endDate) {
                $cashSalesQuery->whereBetween('dispensed_at', [$startDate, $endDate]);
            }

            foreach ($cashSalesQuery->get() as $cashSale) {
                $medicationId = $cashSale->medication_id;
                if ($medicationId) {
                    if (!isset($medicationConsumption[$medicationId])) {
                        $medicationConsumption[$medicationId] = [
                            'medication_id' => $medicationId,
                            'medication_name' => $cashSale->medication->name ?? 'Unknown',
                            'total_quantity' => 0
                        ];
                    }
                    $medicationConsumption[$medicationId]['total_quantity'] += $cashSale->quantity_dispensed ?? 0;
                }
            }

            // Sort by total quantity (descending) and limit
            uasort($medicationConsumption, function($a, $b) {
                return $b['total_quantity'] <=> $a['total_quantity'];
            });

            $topMedications = array_slice(array_values($medicationConsumption), 0, $limit);

            return collect($topMedications);

        } catch (Exception $e) {
            Log::error('Error in getTopConsumedMedications: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get patient consumption history (read-only)
     */
    public function getPatientConsumptionHistory($patientId, $limit = 50)
    {
        try {
            $consumptionHistory = [];

            // From investigations
            $investigations = Investigation::where('patient_id', $patientId)
                ->whereNotNull('batches_used')
                ->orderBy('created_at', 'desc')
                ->limit($limit/3)
                ->get();

            foreach ($investigations as $investigation) {
                foreach ($investigation->batches_used ?? [] as $batch) {
                    $consumptionHistory[] = [
                        'type' => 'investigation',
                        'source_id' => $investigation->id,
                        'date' => $batch['consumed_at'] ?? $investigation->created_at,
                        'medication_id' => $batch['medication_id'] ?? null,
                        'medication_name' => $batch['medication_name'] ?? 'Unknown',
                        'batch' => $batch['batch'] ?? null,
                        'quantity' => $batch['quantity_used'] ?? 0,
                        'reference' => "Investigation #{$investigation->id}",
                        'location' => $batch['location_name'] ?? null
                    ];
                }
            }

            // From prescriptions
            $prescriptions = Prescription::where('patient_id', $patientId)
                ->where('status', 'dispensed')
                ->whereNotNull('batches_used')
                ->orderBy('dispensed_at', 'desc')
                ->limit($limit/3)
                ->get();

            foreach ($prescriptions as $prescription) {
                foreach ($prescription->batches_used ?? [] as $batch) {
                    $consumptionHistory[] = [
                        'type' => 'prescription',
                        'source_id' => $prescription->id,
                        'date' => $prescription->dispensed_at,
                        'medication_id' => $prescription->medication_id,
                        'medication_name' => $prescription->medication->name ?? 'Unknown',
                        'batch_number' => $batch['batch_number'] ?? null,
                        'quantity' => $batch['quantity'] ?? 0,
                        'reference' => "Prescription #{$prescription->id}",
                        'expiry_date' => $batch['expiry_date'] ?? null
                    ];
                }
            }

            // From cash sales (if patient_id is tracked in cash sales)
            $cashSales = MedicationCashSaleItem::whereHas('cashSale', function($query) use ($patientId) {
                    $query->where('patient_id', $patientId);
                })
                ->where('status', 'dispensed')
                ->whereNotNull('batches_used')
                ->orderBy('dispensed_at', 'desc')
                ->limit($limit/3)
                ->get();

            foreach ($cashSales as $cashSale) {
                foreach ($cashSale->batches_used ?? [] as $batch) {
                    $consumptionHistory[] = [
                        'type' => 'cash_sale',
                        'source_id' => $cashSale->id,
                        'date' => $cashSale->dispensed_at,
                        'medication_id' => $cashSale->medication_id,
                        'medication_name' => $cashSale->medication->name ?? 'Unknown',
                        'batch_number' => $batch['batch_number'] ?? null,
                        'quantity' => $batch['quantity'] ?? 0,
                        'reference' => "Cash Sale #{$cashSale->id}",
                        'expiry_date' => $batch['expiry_date'] ?? null
                    ];
                }
            }

            // Sort by date (newest first)
            usort($consumptionHistory, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });

            return array_slice($consumptionHistory, 0, $limit);

        } catch (Exception $e) {
            Log::error("Error retrieving patient consumption history: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get categorized recent consumptions by service type
     */
    public function getCategorizedRecentConsumptions($limit = 5)
    {
        try {
            return [
                'lab_investigations' => $this->getRecentLabInvestigations($limit),
                'nursing_procedures' => $this->getRecentNursingProcedures($limit),
                'radiology_investigations' => $this->getRecentRadiologyInvestigations($limit),
                'consultation_prescriptions' => $this->getRecentConsultationPrescriptions($limit),
                'medication_cash_sales' => $this->getRecentMedicationCashSales($limit)
            ];
        } catch (Exception $e) {
            Log::error("Error retrieving categorized consumptions: " . $e->getMessage());
            return [
                'lab_investigations' => collect(),
                'nursing_procedures' => collect(),
                'radiology_investigations' => collect(),
                'consultation_prescriptions' => collect(),
                'medication_cash_sales' => collect()
            ];
        }
    }

    /**
     * Get recent lab investigations (service_category = Laboratory)
     */
    private function getRecentLabInvestigations($limit = 5)
    {
        try {
            return Investigation::with(['patient', 'medicalService.serviceCategory', 'investigationConsumptions.medication'])
                ->whereHas('medicalService.serviceCategory', function($q) {
                    $q->where('name', 'LIKE', '%Laboratory%')
                      ->orWhere('name', 'LIKE', '%Lab%');
                })
                ->whereHas('investigationConsumptions')
                ->latest()
                ->limit($limit)
                ->get();
        } catch (Exception $e) {
            Log::error("Error getting lab investigations: " . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get recent nursing procedures (service_category = Procedures)
     */
    private function getRecentNursingProcedures($limit = 5)
    {
        try {
            return Investigation::with(['patient', 'medicalService.serviceCategory', 'investigationConsumptions.medication'])
                ->whereHas('medicalService.serviceCategory', function($q) {
                    $q->where('name', 'LIKE', '%Procedure%')
                      ->orWhere('name', 'LIKE', '%Nursing%');
                })
                ->whereHas('investigationConsumptions')
                ->latest()
                ->limit($limit)
                ->get();
        } catch (Exception $e) {
            Log::error("Error getting nursing procedures: " . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get recent radiology investigations (service_category = Radiology)
     */
    private function getRecentRadiologyInvestigations($limit = 5)
    {
        try {
            return Investigation::with(['patient', 'medicalService.serviceCategory', 'investigationConsumptions.medication'])
                ->whereHas('medicalService.serviceCategory', function($q) {
                    $q->where('name', 'LIKE', '%Radiology%')
                      ->orWhere('name', 'LIKE', '%Imaging%');
                })
                ->whereHas('investigationConsumptions')
                ->latest()
                ->limit($limit)
                ->get();
        } catch (Exception $e) {
            Log::error("Error getting radiology investigations: " . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get recent consultation prescriptions (prescriptions table)
     */
    private function getRecentConsultationPrescriptions($limit = 5)
    {
        try {
            return Prescription::with(['patient', 'medication'])
                ->whereNotNull('medication_id')
                ->where('status', 'dispensed')
                ->latest('dispensed_at')
                ->limit($limit)
                ->get();
        } catch (Exception $e) {
            Log::error("Error getting consultation prescriptions: " . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get recent medication cash sales (medication_cash_sale_items table)
     * Note: Cash sales don't necessarily have registered patients
     */
    private function getRecentMedicationCashSales($limit = 5)
    {
        try {
            return MedicationCashSaleItem::with(['medication', 'cashSale'])
                ->whereNotNull('dispensed_at')
                ->where('status', 'dispensed')
                ->latest('dispensed_at')
                ->limit($limit)
                ->get();
        } catch (Exception $e) {
            Log::error("Error getting medication cash sales: " . $e->getMessage());
            return collect();
        }
    }
}
