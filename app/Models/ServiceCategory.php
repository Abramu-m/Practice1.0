<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    use HasFactory;

    const LABORATORY = 1;
    const PROCEDURE = 2;
    const SPECIALIZED_INVESTIGATIONS = 3;
    const OTHERS = 4;

    protected $table = 'service_categories'; // Better than 'service_types'
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Get medical services of this category
     */
    public function medicalServices()
    {
        return $this->hasMany(MedicalService::class);
    }

    /**
     * Get result templates for this category
     */
    public function resultTemplates()
    {
        return $this->hasMany(ResultTemplate::class);
    }

    /**
     * Check if service category is active
     */
    public function isActive()
    {
        return $this->is_active === true;
    }

    /**
     * Scope for active service categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered service categories
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }
}
