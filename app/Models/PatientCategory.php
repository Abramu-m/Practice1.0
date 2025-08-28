<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
    'code',
        'type',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function patients()
    {
        return $this->hasMany(Patient::class, 'patient_category');
    }

    /**
     * Get medication pricing for this category
     */
    public function medicationPricing()
    {
        return $this->hasMany(MedicationPricing::class);
    }

    /**
     * Get active medication pricing
     */
    public function activeMedicationPricing()
    {
        return $this->hasMany(MedicationPricing::class)->active()->current();
    }
}
