<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VitalSigns extends Model
{
    use HasFactory;

    protected $table = 'vital_signs';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'consultation_id',
        'visit_id',
        'patient_id',
        'pulse_rate',
        'temperature',
        'respiratory_rate',
        'weight',
        'height',
        'bmi',
        'oxygen_saturation',
        'systolic_bp',
        'diastolic_bp',
        'muac', // Mid-Upper Arm Circumference
        'ofc',  // Occipital Frontal Circumference
        'notes',
        'recorded_by',
        'recorded_at',
        'updated_by',
        'status'
    ];

    protected $casts = [
        'pulse_rate' => 'decimal:1',
        'temperature' => 'decimal:1',
        'respiratory_rate' => 'integer',
        'weight' => 'decimal:1',
        'height' => 'decimal:1',
        'bmi' => 'decimal:1',
        'oxygen_saturation' => 'decimal:1',
        'systolic_bp' => 'integer',
        'diastolic_bp' => 'integer',
        'recorded_at' => 'datetime',
        'status' => 'boolean'
    ];

    /**
     * Get the consultation that owns the vital signs
     */
    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    /**
     * Get the patient that owns the vital signs
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    /**
     * Get the visit that owns the vital signs
     */
    public function visit()
    {
        return $this->belongsTo(PatientVisit::class, 'visit_id');
    }

    /**
     * Get the user who recorded the vital signs
     */
    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Get the user who last updated the vital signs
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Calculate BMI if weight and height are provided
     */
    public function calculateBMI()
    {
        if ($this->weight && $this->height) {
            $weight = floatval($this->weight);
            $height = floatval($this->height) / 100; // Convert cm to meters
            
            if ($height > 0) {
                return round($weight / ($height * $height), 1);
            }
        }
        return null;
    }

    /**
     * Get blood pressure reading
     */
    public function getBloodPressureAttribute()
    {
        if ($this->systolic_bp && $this->diastolic_bp) {
            return $this->systolic_bp . '/' . $this->diastolic_bp;
        }
        return null;
    }

    /**
     * Get BMI category
     */
    public function getBMICategoryAttribute()
    {
        $bmi = floatval($this->bmi);
        
        if ($bmi < 18.5) return 'Underweight';
        if ($bmi < 25) return 'Normal';
        if ($bmi < 30) return 'Overweight';
        return 'Obese';
    }

    /**
     * Automatically calculate BMI before saving
     */
    protected static function booted()
    {
        static::saving(function ($vitals) {
            if ($vitals->weight && $vitals->height) {
                $vitals->bmi = $vitals->calculateBMI();
            }
        });
    }
}
