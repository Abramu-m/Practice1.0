<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'code',
        'type',
        'tariffs_table',
        'copay_policy',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function patients()
    {
        return $this->hasMany(Patient::class, 'patient_category');
    }

    /**
     * Patient visits recorded against this category.
     */
    public function visits()
    {
        return $this->hasMany(PatientVisit::class, 'visit_category');
    }

    /**
     * Medication cash sales recorded against this category.
     */
    public function medicationCashSales()
    {
        return $this->hasMany(MedicationCashSale::class, 'patient_category_id');
    }

    /**
     * Visit types allowed for this patient category.
     * A visit type with no categories assigned is available to all categories.
     */
    public function visitTypes()
    {
        return $this->belongsToMany(VisitType::class, 'patient_category_visit_type');
    }
}
