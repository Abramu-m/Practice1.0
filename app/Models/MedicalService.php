<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalService extends Model
{
    use HasFactory;

    // Result template types
    const TEMPLATE_SIMPLE_PROCEDURE = 'simple_procedure';
    const TEMPLATE_VITAL_OBSERVATIONS = 'vital_observations';
    const TEMPLATE_COMPLEX_FORM = 'complex_form';
    const TEMPLATE_IMAGING = 'imaging';
    const TEMPLATE_GENERAL_PROCEDURE = 'general_procedure';
    const TEMPLATE_SIMPLE_LAB = 'simple_lab';
    const TEMPLATE_CD4 = 'cd4';
    const TEMPLATE_TB = 'tb';
    const TEMPLATE_GENERAL_LAB = 'general_lab';

    protected $table = 'medical_services'; // Better than 'services'
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'description',
        'service_category_id',
        'requires_sample',
        'sample_type',
        'turnaround_time_hours',
        'preparation_instructions',
        'requires_form',
        'form_type',
        'result_template_id',
        'min_value',
        'max_value',
        'unit',
        'is_active'
    ];

    protected $casts = [
        'turnaround_time_hours' => 'integer',
        'requires_sample' => 'boolean',
        'requires_form' => 'boolean',
        'is_active' => 'boolean',
        'min_value' => 'decimal:1',
        'max_value' => 'decimal:1'
    ];

    /**
     * Get all available result template types
     */
    public static function getResultTemplateTypes(): array
    {
        return [
            self::TEMPLATE_SIMPLE_PROCEDURE => 'Simple Procedure',
            self::TEMPLATE_VITAL_OBSERVATIONS => 'Vital Observations',
            self::TEMPLATE_COMPLEX_FORM => 'Complex Form',
            self::TEMPLATE_IMAGING => 'Imaging Results',
            self::TEMPLATE_GENERAL_PROCEDURE => 'General Procedure',
            self::TEMPLATE_SIMPLE_LAB => 'Simple Lab',
            self::TEMPLATE_CD4 => 'CD4 Count',
            self::TEMPLATE_TB => 'TB Investigation',
            self::TEMPLATE_GENERAL_LAB => 'General Lab'
        ];
    }

    /**
     * Get the service category
     */
    public function serviceCategory()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    /**
     * Get the result template
     */
    public function resultTemplate()
    {
        return $this->belongsTo(ResultTemplate::class);
    }

    /**
     * Get consumable requirements for this medical service
     */
    public function consumableRequirements()
    {
        return $this->hasMany(InvestigationConsumable::class);
    }

    /**
     * Get active consumable requirements for this medical service
     */
    public function activeConsumableRequirements()
    {
        return $this->hasMany(InvestigationConsumable::class)->where('is_active', true);
    }

    /**
     * Get investigations for this service
     */
    public function investigations()
    {
        return $this->hasMany(Investigation::class);
    }

    /**
     * Get pricing records for this service
     */
    public function pricing()
    {
        return $this->hasMany(MedicalServicePricing::class);
    }

    /**
     * Get current active pricing for this service
     */
    public function currentPricing()
    {
        return $this->hasMany(MedicalServicePricing::class)
                   ->active()
                   ->current();
    }

    /**
     * Get the effective price after insurance coverage
     */
    public function getEffectivePriceAttribute(): float
    {
        return $this->price - $this->insurance_covered_amount;
    }

    /**
     * Get turnaround time in readable format
     */
    public function getTurnaroundTimeReadableAttribute(): string
    {
        if (!$this->turnaround_time_hours) {
            return 'Not specified';
        }

        if ($this->turnaround_time_hours < 24) {
            return $this->turnaround_time_hours . ' hours';
        }

        $days = floor($this->turnaround_time_hours / 24);
        $hours = $this->turnaround_time_hours % 24;

        if ($hours == 0) {
            return $days . ' day' . ($days > 1 ? 's' : '');
        }

        return $days . ' day' . ($days > 1 ? 's' : '') . ' ' . $hours . ' hours';
    }

    /**
     * Check if service requires preparation
     */
    public function getRequiresPreparationAttribute(): bool
    {
        return !empty($this->preparation_instructions);
    }

    /**
     * Scope for active services
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for laboratory services
     */
    public function scopeLaboratory($query)
    {
        return $query->whereHas('serviceCategory', function ($q) {
            $q->where('name', 'LIKE', '%Laboratory%');
        });
    }

    /**
     * Scope for radiology services
     */
    public function scopeRadiology($query)
    {
        return $query->whereHas('serviceCategory', function ($q) {
            $q->where('name', 'LIKE', '%Radiology%');
        });
    }

    /**
     * Scope for services that require samples
     */
    public function scopeRequiresSample($query)
    {
        return $query->where('requires_sample', true);
    }

    /**
     * Scope for urgent services (quick turnaround)
     */
    public function scopeUrgent($query)
    {
        return $query->where('turnaround_time_hours', '<=', 4);
    }

    /**
     * Scope for services by category
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('service_category_id', $categoryId);
    }
}
