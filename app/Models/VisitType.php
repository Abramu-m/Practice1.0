<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitType extends Model
{
    protected $fillable = ['description', 'nhif_visit_type_code'];

    protected $casts = [
        'nhif_visit_type_code' => 'integer',
    ];

    /**
     * Get the visits associated with the visit type.
     */
    public function visits()
    {
        return $this->hasMany(PatientVisit::class, 'visit_type');
    }

    /**
     * Patient categories allowed to use this visit type.
     * An empty relation means the visit type is available to all categories.
     */
    public function patientCategories()
    {
        return $this->belongsToMany(PatientCategory::class, 'patient_category_visit_type');
    }
}
