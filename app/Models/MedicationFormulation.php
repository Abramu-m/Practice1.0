<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicationFormulation extends Model
{
    use HasFactory;

    protected $table = 'medication_formulations';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Status constants
    const STATUS_ACTIVE = true;
    const STATUS_INACTIVE = false;

    /**
     * Get medications that use this formulation
     */
    public function medications()
    {
        return $this->hasMany(Medication::class, 'formulation_id');
    }

    /**
     * Scope to get only active formulations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get inactive formulations
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Get all active formulations for dropdown/select options
     */
    public static function getActiveOptions()
    {
        return static::active()
                     ->orderBy('description')
                     ->pluck('description', 'id')
                     ->toArray();
    }

    /**
     * Check if formulation is being used by any medications
     */
    public function isInUse()
    {
        return $this->medications()->exists();
    }
}
