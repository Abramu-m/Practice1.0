<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MedicalServiceInsuranceMap extends Model
{
    use HasFactory;

    protected $table = 'medical_service_insurance_map';

    protected $fillable = [
        'medical_service_id',
        'patient_category_id',
        'insurance_item_code',
    ];

    /**
     * Medical service relationship
     */
    public function medicalService()
    {
        return $this->belongsTo(MedicalService::class);
    }

    /**
     * Patient category relationship
     */
    public function patientCategory()
    {
        return $this->belongsTo(PatientCategory::class);
    }
}
