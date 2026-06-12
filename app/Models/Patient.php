<?php

namespace App\Models;

use App\Models\Concerns\Syncable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory, Syncable;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'contact',
        'email',
        'residence',
        'occupation',
        'nida',
        'patient_category',
        'card_number',
        'membership_number',
        'vote',
        'SchemeID',
        'ProductCode',
        'PackageID',
        'HasSupplementary',
        'SchemeName',
        'mtuha_new',
        'created_by',
        'status',
        'legacy_mrn'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'SchemeID' => 'integer',
        'PackageID' => 'integer',
        'legacy_mrn' => 'string',
    ];

    public function category() {
        return $this->belongsTo(\App\Models\PatientCategory::class, 'patient_category');
    }

    public function patientCategory() {
        return $this->belongsTo(\App\Models\PatientCategory::class, 'patient_category');
    }

    public function creator() {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name);
    }

    /**
     * Get patient number (formatted MR number)
     */
    public function getPatientNumberAttribute()
    {
        return $this->generateMrNumber();
    }

    /**
     * Get medical record number (alias for patient_number)
     */
    public function getMrNumberAttribute()
    {
        return $this->patient_number;
    }

    /**
     * Get registration number (alias for patient_number)
     */
    public function getRegistrationNumberAttribute()
    {
        return $this->patient_number;
    }

    /**
     * Generate formatted MR number
     */
    protected function generateMrNumber()
    {
        if ($this->legacy_mrn) {
            return $this->legacy_mrn;
        }
        $year = $this->created_at ? $this->created_at->format('Y') : date('Y');
        $paddedId = str_pad($this->id, 6, '0', STR_PAD_LEFT);
        return "MR-{$year}-{$paddedId}";
    }

    /**
     * Get consultations for this patient
     */
    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'patient_id', 'id');
    }

    /**
     * Get investigations for this patient
     */
    public function investigations()
    {
        return $this->hasMany(Investigation::class, 'patient_id', 'id');
    }

    /**
     * Get prescriptions for this patient
     */
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'patient_id', 'id');
    }

    /**
     * Get patient visits
     */
    public function visits()
    {
        return $this->hasMany(PatientVisit::class, 'patient', 'id');
    }

    /**
     * Get patient's past medical history
     */
    public function pastMedicalHistory()
    {
        return $this->hasOne(PastMedicalHistory::class);
    }

    /**
     * Structured allergies (drug/environmental) captured in allergies table
     */
    public function allergies()
    {
        return $this->hasMany(Allergy::class);
    }

    /**
     * Get patient's NHIF member record
     */
    public function nhifMember()
    {
        return $this->hasOne(NhifMember::class);
    }

    /**
     * Get patient's NHIF claims
     */
    public function nhifClaims()
    {
        return $this->hasMany(NhifClaim::class);
    }

    /**
     * Check if patient has active NHIF membership
     */
    public function hasActiveNhifMembership(): bool
    {
        return $this->nhifMember && $this->nhifMember->isActive();
    }

    /**
     * Get patient's NHIF card number
     */
    public function getNhifCardNumberAttribute(): ?string
    {
        return $this->nhifMember?->card_no ?? $this->card_number;
    }

    /**
     * Scope to search by MR number
     */
    public function scopeByMrNumber($query, $mrNumber)
    {
        // Extract ID from MR number format (MR-YYYY-XXXXXX)
        if (preg_match('/MR-\d{4}-(\d+)/', $mrNumber, $matches)) {
            return $query->where('id', intval($matches[1]));
        }
        
        // Fallback: search by raw number if it's numeric
        if (is_numeric($mrNumber)) {
            return $query->where('id', $mrNumber);
        }
        
        return $query->whereRaw('1 = 0'); // Return no results if format is invalid
    }

    /**
     * Find patient by MR number
     */
    public static function findByMrNumber($mrNumber)
    {
        return static::byMrNumber($mrNumber)->first();
    }

    /**
     * Active visit relationship (for eager loading)
     */
    public function activeVisit()
    {
        return $this->hasOne(PatientVisit::class, 'patient', 'id')
            ->whereIn('visit_status', [0, 1])
            ->where('visit_date', '>=', now()->subDays(7))
            ->latest('visit_date');
    }

    /**
     * Get the patient's active visit (status 0 or 1 and within last 7 days)
     * Returns the most recent active visit that's not older than 7 days
     */
    public function getActiveVisitAttribute()
    {
        // If relationship is already loaded, use it
        if ($this->relationLoaded('activeVisit')) {
            return $this->getRelation('activeVisit');
        }
        
        // Otherwise query it
        return $this->visits()
            ->whereIn('visit_status', [0, 1])
            ->where('visit_date', '>=', now()->subDays(7))
            ->orderByDesc('visit_date')
            ->first();
    }

    /**
     * Get the patient's recent visit (within last 7 days, any status)
     * Returns the most recent visit from the last 7 days regardless of status
     */
    public function getRecentVisitAttribute()
    {
        return $this->visits()
            ->where('visit_date', '>=', now()->subDays(7))
            ->orderByDesc('visit_date')
            ->first();
    }

    /**
     * Get the patient's last visit (most recent visit ever, any date/status)
     * Returns the most recent visit regardless of date or status
     */
    public function getLastVisitAttribute()
    {
        return $this->visits()
            ->orderByDesc('visit_date')
            ->orderByDesc('id')
            ->first();
    }

}
