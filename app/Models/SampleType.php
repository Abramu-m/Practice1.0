<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SampleType extends Model
{
    use HasFactory;

    protected $table = 'sample_types';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'code',
        'description',
        'container_type',
        'color_code',
        'volume_ml',
        'collection_instructions',
        'storage_requirements',
        'stability_hours',
        'requires_fasting',
        'is_active'
    ];

    protected $casts = [
        'volume_ml' => 'decimal:2',
        'stability_hours' => 'integer',
        'requires_fasting' => 'boolean',
        'is_active' => 'boolean'
    ];

    /**
     * Get medical services that use this sample type
     */
    public function medicalServices()
    {
        return $this->hasMany(MedicalService::class, 'sample_type_id');
    }

    /**
     * Get stability in readable format
     */
    public function getStabilityReadableAttribute(): string
    {
        if (!$this->stability_hours) {
            return 'Not specified';
        }

        if ($this->stability_hours < 24) {
            return $this->stability_hours . ' hours';
        }

        $days = floor($this->stability_hours / 24);
        $hours = $this->stability_hours % 24;

        if ($hours == 0) {
            return $days . ' day' . ($days > 1 ? 's' : '');
        }

        return $days . ' day' . ($days > 1 ? 's' : '') . ' ' . $hours . ' hours';
    }

    /**
     * Check if sample type is active
     */
    public function isActive()
    {
        return $this->is_active === true;
    }

    /**
     * Scope for active sample types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for sample types that require fasting
     */
    public function scopeRequiresFasting($query)
    {
        return $query->where('requires_fasting', true);
    }
}
