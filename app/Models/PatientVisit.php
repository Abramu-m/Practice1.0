<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientVisit extends Model
{
    protected $fillable = [
        'patient',
        'visit_type',
        'visit_date',
        'visit_category',
        'doctor',
        'patient_category_id',
        'amount_cash',
        'amount_covered',
        'sic_no',
        'authorization_no',
        'nhif_reference_no',
        'folio_item_id',
        'created_by',
        'created_on',
        'visit_status',
        'post_status',
        'vital_status',
        'pitc_at',
        'vitals_at',
        'consulted_at',
        'resulted_at',
        'signature'
    ];

    // Define relationships if needed
    public function patientInfo() { 
        return $this->belongsTo(Patient::class, 'patient', 'id'); 
    }

    // Backwards-compatible alias: many views/controllers reference `patient`
    public function patient() {
        return $this->patientInfo();
    }
    
    public function visitType() { 
        return $this->belongsTo(VisitType::class, 'visit_type');
    }
    
    public function visitCategory() { 
        return $this->belongsTo(PatientCategory::class, 'visit_category'); 
    }
    
    public function doctorInfo() { 
        return $this->belongsTo(Doctor::class, 'doctor', 'doctor_id'); 
    }

    // Backwards-compatible alias: provide a simple `doctor()` method so code
    // can consistently reference $visit->doctor in new code while older
    // templates/controllers that use doctorInfo() keep working.
    public function doctor()
    {
        return $this->doctorInfo();
    }
    
    public function createdBy() { 
        return $this->belongsTo(User::class, 'created_by'); 
    }

    public function vitalSigns()
    {
        return $this->hasMany(VitalSigns::class, 'visit_id');
    }

    //casts
    protected $casts = [
        'visit_date' => 'datetime',
        'created_on' => 'datetime',
        'pitc_at' => 'datetime',
        'vitals_at' => 'datetime',
        'consulted_at' => 'datetime',
        'resulted_at' => 'datetime',
    ];

    /**
     * Get the visit status options
     */
    public static function getVisitStatusOptions()
    {
        return [
            0 => 'Waiting',
            1 => 'In Treatment',
            2 => 'Discharged'
        ];
    }

    /**
     * Get the vital status options
     */
    public static function getVitalStatusOptions()
    {
        return [
            0 => 'Not Taken',
            1 => 'Vital Signs Taken'
        ];
    }

    /**
     * Get the visit status label
     */
    public function getVisitStatusLabelAttribute()
    {
        $options = self::getVisitStatusOptions();
        return $options[$this->visit_status] ?? 'Unknown';
    }

    /**
     * Get the vital status label
     */
    public function getVitalStatusLabelAttribute()
    {
        $options = self::getVitalStatusOptions();
        return $options[$this->vital_status] ?? 'Unknown';
    }

    /**
     * Get the visit status badge class for UI
     */
    public function getVisitStatusBadgeClassAttribute()
    {
        switch ($this->visit_status) {
            case 0:
                return 'badge-warning'; // Waiting - yellow/orange
            case 1:
                return 'badge-info'; // In treatment - blue
            case 2:
                return 'badge-success'; // Discharged - green
            default:
                return 'badge-secondary';
        }
    }

    /**
     * Get the vital status badge class for UI
     */
    public function getVitalStatusBadgeClassAttribute()
    {
        switch ($this->vital_status) {
            case 0:
                return 'badge-danger'; // Not taken - red
            case 1:
                return 'badge-success'; // Taken - green
            default:
                return 'badge-secondary';
        }
    }

    /**
     * Get consultation for this visit
     */
    public function consultation()
    {
        return $this->hasOne(Consultation::class, 'visit_id', 'id');
    }

    /**
     * Get prescriptions for this visit
     */
    public function prescriptions()
    {
        if (!$this->visit_date) {
            return $this->hasManyThrough(
                Prescription::class,
                Consultation::class,
                'patient_id', // Foreign key on consultation table
                'consultation_id', // Foreign key on prescription table
                'patient', // Local key on patient_visits table
                'cscsid' // Local key on consultation table
            )->whereRaw('1 = 0'); // Return empty relationship if no visit_date
        }
        
        return $this->hasManyThrough(
            Prescription::class,
            Consultation::class,
            'patient_id', // Foreign key on consultation table
            'consultation_id', // Foreign key on prescription table
            'patient', // Local key on patient_visits table
            'cscsid' // Local key on consultation table
        )->where('consultation.doctor_id', $this->doctor)
         ->whereDate('consultation.consultation_date', $this->visit_date);
    }

    /**
     * Get lab orders for this visit
     */
    public function labOrders()
    {
        if (!$this->visit_date) {
            return $this->hasManyThrough(
                Investigation::class,
                Consultation::class,
                'patient_id', // Foreign key on consultation table
                'consultation_id', // Foreign key on investigation table
                'patient', // Local key on patient_visits table
                'cscsid' // Local key on consultation table
            )->whereRaw('1 = 0'); // Return empty relationship if no visit_date
        }
        
        return $this->hasManyThrough(
            Investigation::class,
            Consultation::class,
            'patient_id', // Foreign key on consultation table
            'consultation_id', // Foreign key on investigation table
            'patient', // Local key on patient_visits table
            'cscsid' // Local key on consultation table
        )->where('consultation.doctor_id', $this->doctor)
         ->whereDate('consultation.cscreatedon', $this->visit_date);
    }

    /**
     * Get investigations for this visit using visit_id relationship
     */
    public function investigations()
    {
        return $this->hasMany(Investigation::class, 'visit_id', 'id');
    }

    /**
     * Get prescriptions for this visit using visit_id relationship
     */
    public function prescriptionsViaVisit()
    {
        return $this->hasMany(Prescription::class, 'visit_id', 'id');
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Handle cascading deletes for relationships without ON DELETE CASCADE
        static::deleting(function ($patientVisit) {
            // Delete investigations manually (due to missing ON DELETE CASCADE)
            $patientVisit->investigations()->delete();
            
            // Delete prescriptions manually (due to missing ON DELETE CASCADE)  
            $patientVisit->prescriptionsViaVisit()->delete();
        });
    }
}
