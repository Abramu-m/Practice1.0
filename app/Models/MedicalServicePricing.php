<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalServicePricing extends Model
{
    use HasFactory;

    protected $table = 'medical_services_pricing';

    protected $fillable = [
        'medical_service_id',
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
     * Get the medical service
     */
    public function medicalService()
    {
        return $this->belongsTo(MedicalService::class);
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
     * Scope for active pricing only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for current effective pricing
     */
    public function scopeCurrent($query)
    {
        $today = now()->toDateString();
        return $query->where(function ($q) use ($today) {
            $q->where('effective_from', '<=', $today)
              ->where(function ($subQ) use ($today) {
                  $subQ->whereNull('effective_to')
                       ->orWhere('effective_to', '>=', $today);
              });
        });
    }

    /**
     * Scope for a specific medical service
     */
    public function scopeForService($query, $serviceId)
    {
        return $query->where('medical_service_id', $serviceId);
    }

    /**
     * Scope for a specific patient category
     */
    public function scopeForCategory($query, $categoryId)
    {
        return $query->where('patient_category_id', $categoryId);
    }

    // Helper Methods

    /**
     * Check if pricing is currently effective
     */
    public function isCurrentlyEffective()
    {
        $today = now()->toDateString();
        
        if (!$this->is_active) {
            return false;
        }
        
        if ($this->effective_from && $this->effective_from > $today) {
            return false;
        }
        
        if ($this->effective_to && $this->effective_to < $today) {
            return false;
        }
        
        return true;
    }

    /**
     * Get the effective price for display
     */
    public function getEffectivePriceAttribute()
    {
        return $this->selling_price;
    }

    /**
     * Get pricing status for display
     */
    public function getStatusAttribute()
    {
        if (!$this->is_active) {
            return 'Inactive';
        }
        
        if ($this->isCurrentlyEffective()) {
            return 'Active';
        }
        
        $today = now()->toDateString();
        if ($this->effective_from && $this->effective_from > $today) {
            return 'Future';
        }
        
        return 'Expired';
    }

    /**
     * Static method to get current price for a service and category
     */
    public static function getCurrentPrice($serviceId, $categoryId)
    {
        return static::active()
                    ->current()
                    ->forService($serviceId)
                    ->forCategory($categoryId)
                    ->first();
    }
}
