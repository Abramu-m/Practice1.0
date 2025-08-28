<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestigationConsumption extends Model
{
    use HasFactory;

    protected $fillable = [
        'investigation_id',
        'medication_id',
        'batch_number',
        'quantity_used',
        'cost_per_unit',
        'consumed_from_location_id',
        'consumed_by',
        'consumed_at',
        'notes'
    ];

    protected $casts = [
        'consumed_at' => 'datetime',
        'quantity_used' => 'decimal:2',
        'cost_per_unit' => 'decimal:2',
    ];

    // Relationships

    /**
     * Get the investigation
     */
    public function investigation()
    {
        return $this->belongsTo(Investigation::class);
    }

    /**
     * Get the medication
     */
    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }

    /**
     * Get the store location where the medication was consumed from
     */
    public function consumedFromLocation()
    {
        return $this->belongsTo(StoreLocation::class, 'consumed_from_location_id');
    }

    /**
     * Get the user who consumed the item
     */
    public function consumedBy()
    {
        return $this->belongsTo(User::class, 'consumed_by');
    }

    // Scopes

    /**
     * Scope to filter by patient
     */
    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope to filter by investigation
     */
    public function scopeByInvestigation($query, $investigationId)
    {
        return $query->where('investigation_id', $investigationId);
    }

    /**
     * Scope to filter by department
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('consumed_at', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by consumed user
     */
    public function scopeByConsumedBy($query, $userId)
    {
        return $query->where('consumed_by', $userId);
    }

    /**
     * Scope for medications only
     */
    public function scopeMedicationsOnly($query)
    {
        return $query->whereNotNull('medication_id');
    }

    /**
     * Scope for consumables only
     */
    public function scopeConsumablesOnly($query)
    {
        return $query->whereNotNull('consumable_id');
    }

    /**
     * Scope to get consumption today
     */
    public function scopeToday($query)
    {
        return $query->whereDate('consumed_at', today());
    }

    /**
     * Scope to get consumption this week
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('consumed_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope to get consumption this month
     */
    public function scopeThisMonth($query)
    {
        return $query->whereYear('consumed_at', now()->year)
                    ->whereMonth('consumed_at', now()->month);
    }

    // Accessor methods

    /**
     * Get the item name (medication or consumable)
     */
    public function getItemNameAttribute()
    {
        if ($this->medication) {
            return $this->medication->name;
        }
        
        if ($this->consumable) {
            return $this->consumable->name;
        }
        
        return 'Unknown Item';
    }

    /**
     * Get the item type
     */
    public function getItemTypeAttribute()
    {
        if ($this->medication_id) {
            return 'medication';
        }
        
        if ($this->consumable_id) {
            return 'consumable';
        }
        
        return 'unknown';
    }

    /**
     * Get the item unit
     */
    public function getItemUnitAttribute()
    {
        if ($this->medication) {
            return $this->medication->unit;
        }
        
        if ($this->consumable) {
            return $this->consumable->unit;
        }
        
        return '';
    }

    /**
     * Get consumption cost
     */
    public function getConsumptionCostAttribute()
    {
        $unitCost = 0;
        
        if ($this->locationStock && $this->locationStock->unit_cost) {
            $unitCost = $this->locationStock->unit_cost;
        } elseif ($this->grnItem && $this->grnItem->unit_cost) {
            $unitCost = $this->grnItem->unit_cost;
        }
        
        return $this->quantity_used * $unitCost;
    }

    /**
     * Get consumption summary
     */
    public function getConsumptionSummaryAttribute()
    {
        return sprintf(
            '%s consumed %s %s of %s for %s',
            $this->consumedBy->name ?? 'Unknown User',
            $this->quantity_used,
            $this->item_unit,
            $this->item_name,
            $this->investigation->name ?? 'Unknown Investigation'
        );
    }

    // Helper methods

    /**
     * Check if consumption is for medication
     */
    public function isMedication()
    {
        return !is_null($this->medication_id);
    }

    /**
     * Check if consumption is for consumable
     */
    public function isConsumable()
    {
        return !is_null($this->consumable_id);
    }

    /**
     * Get the item (medication or consumable)
     */
    public function getItem()
    {
        if ($this->medication) {
            return $this->medication;
        }
        
        return $this->consumable;
    }

    /**
     * Create consumption record from location stock
     */
    public static function createFromLocationStock($locationStock, $investigation, $patient, $quantity, $consumedBy, $notes = null)
    {
        $data = [
            'patient_id' => $patient->id,
            'investigation_id' => $investigation->id,
            'location_stock_id' => $locationStock->id,
            'grn_item_id' => $locationStock->grn_item_id,
            'quantity_used' => $quantity,
            'consumed_by' => $consumedBy,
            'consumed_at' => now(),
            'department_id' => $locationStock->department_id,
            'notes' => $notes
        ];

        if ($locationStock->medication_id) {
            $data['medication_id'] = $locationStock->medication_id;
        } else {
            $data['consumable_id'] = $locationStock->consumable_id;
        }

        return self::create($data);
    }

    /**
     * Get consumption statistics for a period
     */
    public static function getConsumptionStats($startDate, $endDate, $departmentId = null)
    {
        $query = self::whereBetween('consumed_at', [$startDate, $endDate]);
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        return [
            'total_consumptions' => $query->count(),
            'total_quantity' => $query->sum('quantity_used'),
            'unique_items' => $query->selectRaw('COUNT(DISTINCT COALESCE(medication_id, consumable_id)) as count')->value('count'),
            'unique_patients' => $query->distinct('patient_id')->count(),
            'unique_investigations' => $query->distinct('investigation_id')->count(),
        ];
    }

    /**
     * Get top consumed items for a period
     */
    public static function getTopConsumedItems($startDate, $endDate, $limit = 10, $departmentId = null)
    {
        $query = self::whereBetween('consumed_at', [$startDate, $endDate])
                    ->selectRaw('
                        COALESCE(medication_id, consumable_id) as item_id,
                        CASE 
                            WHEN medication_id IS NOT NULL THEN "medication"
                            ELSE "consumable"
                        END as item_type,
                        SUM(quantity_used) as total_quantity,
                        COUNT(*) as consumption_count
                    ')
                    ->groupByRaw('COALESCE(medication_id, consumable_id), item_type')
                    ->orderBy('total_quantity', 'desc')
                    ->limit($limit);
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        return $query->get();
    }

    /**
     * Get investigation consumption report
     */
    public static function getInvestigationReport($investigationId, $startDate, $endDate)
    {
        return self::with(['medication', 'consumable', 'patient', 'consumedBy'])
                  ->where('investigation_id', $investigationId)
                  ->whereBetween('consumed_at', [$startDate, $endDate])
                  ->get()
                  ->groupBy('item_type')
                  ->map(function ($items) {
                      return [
                          'total_quantity' => $items->sum('quantity_used'),
                          'total_cost' => $items->sum('consumption_cost'),
                          'consumption_count' => $items->count(),
                          'items' => $items
                      ];
                  });
    }
}
