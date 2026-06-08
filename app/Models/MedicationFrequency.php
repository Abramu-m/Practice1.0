<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicationFrequency extends Model
{
    use HasFactory;

    protected $table = 'medication_frequencies'; // Better than 'frequency'
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'frequency_name',
        'frequency_code',
        'description',
        'times_per_day',
        'interval_hours',
        'administration_times',
        'is_active',
        'display_order'
    ];

    protected $casts = [
        'administration_times' => 'array',
        'is_active' => 'boolean',
        'times_per_day' => 'integer',
        'interval_hours' => 'integer',
        'display_order' => 'integer'
    ];

    /**
     * Get prescriptions using this frequency
     */
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * Check if frequency is active
     */
    public function isActive()
    {
        return $this->is_active === true;
    }

    /**
     * Get display name with description
     */
    public function getDisplayNameAttribute()
    {
        $name = $this->frequency_name;
        if ($this->description && $this->description !== $this->frequency_name) {
            $name .= ' (' . $this->description . ')';
        }
        return $name;
    }

    /**
     * Scope for active frequencies
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('display_order');
    }
}
