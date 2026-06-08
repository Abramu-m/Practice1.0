<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicationCashSaleItem extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_DISPENSED = 'dispensed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'cash_sale_id',
        'medication_id',
        'quantity',
        'dosage',
        'medication_frequency_id',
        'administration_route_id',
        'duration_days',
        'instructions',
        'unit_price',
        'total_price',
        'batches_used',
        'status',
        'dispensing_type',
        'quantity_dispensed',
        'dispensed_at',
        'dispensed_by',
        'cancelled_at',
        'cancelled_by',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'quantity_dispensed' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'batches_used' => 'json',
        'dispensed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'duration_days' => 'integer',
    ];

    // Relationships

    public function cashSale()
    {
        return $this->belongsTo(MedicationCashSale::class, 'cash_sale_id');
    }

    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }

    public function medicationFrequency()
    {
        return $this->belongsTo(MedicationFrequency::class);
    }

    public function administrationRoute()
    {
        return $this->belongsTo(AdministrationRoute::class);
    }

    public function dispenser()
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }

    // Methods

    /**
     * Calculate total price
     */
    public function calculateTotal()
    {
        $this->total_price = $this->quantity * $this->unit_price;
        $this->save();
    }

    /**
     * Get pricing for medication
     */
    public static function getPriceForMedication($medicationId, $categoryId = null)
    {
        $medication = Medication::find($medicationId);
        return $medication ? (float) $medication->selling_price : 0;
    }

    /**
     * Check if can be dispensed
     */
    public function canBeDispensed()
    {
        return $this->status === self::STATUS_PENDING && $this->quantity > 0;
    }

    /**
     * Mark as dispensed with batch information
     */
    public function markAsDispensed($batchesUsed, $quantityDispensed = null, $userId = null)
    {
        // Enforce payment-first policy
        if (!$this->cashSale->is_paid) {
            throw new \Exception('Cannot dispense medication from unpaid cash sale. Payment must be processed first.');
        }

        $this->update([
            'status' => self::STATUS_DISPENSED,
            'quantity_dispensed' => $quantityDispensed ?? $this->quantity,
            'batches_used' => $batchesUsed,
            'dispensed_at' => now(),
            'dispensed_by' => $userId,
        ]);
    }

    /**
     * Check if partially dispensed
     */
    public function isPartiallyDispensed()
    {
        return $this->quantity_dispensed > 0 && $this->quantity_dispensed < $this->quantity;
    }

    /**
     * Check if fully dispensed
     */
    public function isFullyDispensed()
    {
        return $this->quantity_dispensed >= $this->quantity;
    }

    /**
     * Get remaining quantity to dispense
     */
    public function getRemainingQuantityAttribute()
    {
        return $this->quantity - $this->quantity_dispensed;
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_DISPENSED => 'success',
            self::STATUS_CANCELLED => 'danger',
            default => 'secondary'
        };
    }
}
