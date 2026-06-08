<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class StoreStockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_type',
        'item_id', // medication_id or consumable_id
        'store_location_id',
        'from_location_id',
        'to_location_id',
        'batch_id',
        'movement_type', // 'in', 'out', 'transfer', 'adjustment', 'waste'
        'transaction_type', // 'purchase', 'dispensing', 'requisition', 'transfer', 'adjustment', 'waste', 'return', 'consumption', 'disposal'
        'reference_number',
        'reference_id',
        'batch_number',
        'quantity',
        'unit_cost',
        'total_cost',
        'movement_date',
        'balance_before',
        'balance_after',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'movement_date' => 'datetime',
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2'
    ];

    protected $dates = [
        'movement_date'
    ];

    /**
     * Relationships
     */
    public function medication()
    {
        return $this->belongsTo(Medication::class, 'item_id');
    }

    public function storeLocation()
    {
        return $this->belongsTo(StoreLocation::class, 'store_location_id');
    }

    public function fromLocation()
    {
        return $this->belongsTo(StoreLocation::class, 'from_location_id');
    }

    public function toLocation()
    {
        return $this->belongsTo(StoreLocation::class, 'to_location_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeInward($query)
    {
        return $query->where('movement_type', 'inward');
    }

    public function scopeOutward($query)
    {
        return $query->where('movement_type', 'outward');
    }

    public function scopeTransfer($query)
    {
        return $query->where('movement_type', 'transfer');
    }

    public function scopeAdjustment($query)
    {
        return $query->where('movement_type', 'adjustment');
    }

    public function scopeByReference($query, $type, $id = null)
    {
        $query->where('reference_type', $type);
        
        if ($id) {
            $query->where('reference_id', $id);
        }
        
        return $query;
    }

    public function scopeByMedication($query, $medicationId)
    {
        return $query->where('medication_id', $medicationId);
    }

    public function scopeByLocation($query, $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('processed_at', [$startDate, $endDate]);
    }

    /**
     * Accessors
     */
    public function getTotalValueAttribute()
    {
        return $this->quantity * $this->unit_cost;
    }

    public function getMovementDescriptionAttribute()
    {
        $descriptions = [
            'inward' => 'Stock Received',
            'outward' => 'Stock Issued',
            'transfer' => 'Stock Transfer',
            'adjustment' => 'Stock Adjustment'
        ];

        return $descriptions[$this->movement_type] ?? 'Unknown Movement';
    }

    public function getReferenceDescriptionAttribute()
    {
        $descriptions = [
            'grn' => 'Goods Received Note',
            'requisition' => 'Store Requisition',
            'transfer' => 'Stock Transfer',
            'adjustment' => 'Stock Adjustment',
            'disposal' => 'Stock Disposal',
            'prescription' => 'Prescription Dispensing',
            'investigation' => 'Investigation Consumption',
            'procedure' => 'Procedure Consumption'
        ];

        return $descriptions[$this->reference_type] ?? 'Unknown Reference';
    }

    /**
     * Get total inward movements for a medication
     */
    public static function getTotalInward($medicationId, $locationId = null)
    {
        $query = static::inward()->where('medication_id', $medicationId);
        
        if ($locationId) {
            $query->where('location_id', $locationId);
        }
        
        return $query->sum('quantity');
    }

    /**
     * Get total outward movements for a medication
     */
    public static function getTotalOutward($medicationId, $locationId = null)
    {
        $query = static::outward()->where('medication_id', $medicationId);
        
        if ($locationId) {
            $query->where('location_id', $locationId);
        }
        
        return $query->sum('quantity');
    }

    /**
     * Get net balance for a medication
     */
    public static function getNetBalance($medicationId, $locationId = null)
    {
        $inward = static::getTotalInward($medicationId, $locationId);
        $outward = static::getTotalOutward($medicationId, $locationId);
        
        return $inward - $outward;
    }

    /**
     * Get movement history for a medication
     */
    public static function getMovementHistory($medicationId, $locationId = null, $limit = 50)
    {
        $query = static::with(['medication', 'location', 'createdBy'])
            ->where('medication_id', $medicationId)
            ->orderBy('processed_at', 'desc');
        
        if ($locationId) {
            $query->where('location_id', $locationId);
        }
        
        return $query->limit($limit)->get();
    }

    /**
     * Create movement record
     */
    public static function createMovement($data)
    {
        $movement = static::create(array_merge($data, [
            'processed_at' => now(),
            'created_by' => Auth::id() ?? 1
        ]));

        return $movement;
    }
}
