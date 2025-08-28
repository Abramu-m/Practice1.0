<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResultTemplate extends Model
{
    use HasFactory;

    protected $table = 'result_templates';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'code',
        'description',
        'service_category_id',
        'investigation_type',
        'template_fields',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'template_fields' => 'array',
        'service_category_id' => 'integer'
    ];

    /**
     * Get the service category this template belongs to
     */
    public function serviceCategory()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    /**
     * Get medical services that use this template
     */
    public function medicalServices()
    {
        return $this->hasMany(MedicalService::class, 'result_template_id');
    }

    /**
     * Scope for active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for a specific service category
     */
    public function scopeForCategory($query, $categoryId)
    {
        return $query->where('service_category_id', $categoryId);
    }

    /**
     * Scope for ordered templates
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }

    /**
     * Get available template types from MedicalService constants
     */
    public static function getAvailableTemplateTypes()
    {
        return [
            \App\Models\MedicalService::TEMPLATE_SIMPLE_PROCEDURE,
            \App\Models\MedicalService::TEMPLATE_VITAL_OBSERVATIONS,
            \App\Models\MedicalService::TEMPLATE_COMPLEX_FORM,
            \App\Models\MedicalService::TEMPLATE_IMAGING,
            \App\Models\MedicalService::TEMPLATE_GENERAL_PROCEDURE,
            \App\Models\MedicalService::TEMPLATE_SIMPLE_LAB,
            \App\Models\MedicalService::TEMPLATE_CD4,
            \App\Models\MedicalService::TEMPLATE_TB,
            \App\Models\MedicalService::TEMPLATE_GENERAL_LAB,
        ];
    }

    /**
     * Get template types with labels
     */
    public static function getTemplateTypesWithLabels()
    {
        return \App\Models\MedicalService::getResultTemplateTypes();
    }

    /**
     * Check if template is active
     */
    public function isActive()
    {
        return $this->is_active === true;
    }
}
