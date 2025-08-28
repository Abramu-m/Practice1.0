<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestigationTemplateResult extends Model
{
    use HasFactory;

    protected $table = 'investigation_template_results';
    
    protected $fillable = [
        'investigation_id',
        'template_name',
        'template_version',
        'form_data',
        'form_status',
        'metadata',
        'reported_by',
        'reported_at',
        'verified_by',
        'verified_at'
    ];

    protected $casts = [
        'form_data' => 'array',
        'metadata' => 'array',
        'reported_at' => 'datetime',
        'verified_at' => 'datetime'
    ];

    // Form status constants
    const FORM_STATUS_DRAFT = 'draft';
    const FORM_STATUS_PRELIMINARY = 'preliminary';
    const FORM_STATUS_FINAL = 'final';

    /**
     * Get the investigation that owns this template result
     */
    public function investigation()
    {
        return $this->belongsTo(Investigation::class);
    }

    /**
     * Get the user who reported this result
     */
    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /**
     * Get the user who verified this result
     */
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Check if result is finalized
     */
    public function isFinal()
    {
        return $this->form_status === self::FORM_STATUS_FINAL;
    }

    /**
     * Check if result is draft
     */
    public function isDraft()
    {
        return $this->form_status === self::FORM_STATUS_DRAFT;
    }

    /**
     * Get form status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->form_status) {
            self::FORM_STATUS_DRAFT => 'badge-warning',
            self::FORM_STATUS_PRELIMINARY => 'badge-info',
            self::FORM_STATUS_FINAL => 'badge-success',
            default => 'badge-secondary'
        };
    }

    /**
     * Get a specific field value from form data
     */
    public function getFormField($fieldName, $default = null)
    {
        return data_get($this->form_data, $fieldName, $default);
    }

    /**
     * Set a specific field value in form data
     */
    public function setFormField($fieldName, $value)
    {
        $formData = $this->form_data ?? [];
        data_set($formData, $fieldName, $value);
        $this->form_data = $formData;
    }
}
