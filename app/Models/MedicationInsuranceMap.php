<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MedicationInsuranceMap extends Model
{
    use HasFactory;

    protected $table = 'medication_insurance_map';

    protected $fillable = [
        'medication_id',
        'patient_category_id',
        'insurance_item_code',
    ];

    /**
     * Medication relationship
     */
    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }

    /**
     * Patient category relationship
     */
    public function patientCategory()
    {
        return $this->belongsTo(PatientCategory::class);
    }
}
