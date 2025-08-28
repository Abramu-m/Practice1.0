<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicationPricing extends Model
{
    use HasFactory;

    protected $table = 'medication_pricing';

    protected $fillable = [
        'medication_id',
        'patient_category_id',
        'selling_price',
        'markup_percentage',
        'discount_percentage',
        'is_active',
        'effective_from',
        'effective_to',
        'notes'
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'markup_percentage' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date'
    ];

    // Relationships
    
    /**
     * Get the medication
     */
    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }

    /**
     * Get the patient category
     */
    public function patientCategory()
    {
        return $this->belongsTo(PatientCategory::class);
    }

    // Scopes
    
    /**
     * Scope for active pricing
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for current pricing (within effective dates)
     */
    public function scopeCurrent($query)
    {
        $today = now()->toDateString();
        return $query->where(function ($q) use ($today) {
            $q->where('effective_from', '<=', $today)
              ->where(function ($subQ) use ($today) {
                  $subQ->where('effective_to', '>=', $today)
                       ->orWhereNull('effective_to');
              });
        });
    }

    /**
     * Scope for specific medication and category
     */
    public function scopeForMedicationAndCategory($query, $medicationId, $categoryId)
    {
        return $query->where('medication_id', $medicationId)
                     ->where('patient_category_id', $categoryId);
    }

    // Methods
    
    /**
     * Calculate profit margin
     * Note: This method is disabled since unit_cost is now managed at batch level in medication_ledger
     * To calculate profit margin, you would need to determine which batch(es) to use for cost calculation
     */
    public function getProfitMarginAttribute()
    {
        // This calculation is now complex since costs vary by batch
        // You might want to calculate based on average cost, FIFO, LIFO, etc.
        // Consider implementing this logic in a service class
        return null;
    }

    /**
     * Check if pricing is currently effective (today)
     */
    public function isCurrent()
    {
        return $this->isEffectiveOn(now());
    }

    /**
     * Get effective price for a given date
     */
    public function isEffectiveOn($date)
    {
        $checkDate = is_string($date) ? $date : $date->toDateString();
        
        $effectiveFrom = $this->effective_from ? $this->effective_from->toDateString() : null;
        $effectiveTo = $this->effective_to ? $this->effective_to->toDateString() : null;
        
        return (!$effectiveFrom || $checkDate >= $effectiveFrom) &&
               (!$effectiveTo || $checkDate <= $effectiveTo);
    }

    /**
     * Get discounted price
     */
    public function getDiscountedPriceAttribute()
    {
        $discount = $this->selling_price * ($this->discount_percentage / 100);
        return $this->selling_price - $discount;
    }
}
