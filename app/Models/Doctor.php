<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $primaryKey = 'doctor_id';
    public $incrementing = false;
    protected $keyType = 'int';
    
    protected $fillable = [
        'doctor_id',
        'designation',
        'specialization',
        'mct_number',
        'drsignature',
        'created_by',
        'status'
    ];

    /**
     * Get the route key name for Laravel model binding.
     */
    public function getRouteKeyName()
    {
        return 'doctor_id';
    }

    /**
     * Get the visits associated with the doctor.
     */
    public function visits()
    {
        return $this->hasMany(\App\Models\PatientVisit::class, 'doctor', 'doctor_id');
    }

    /**
     * Get the user who created the doctor record.
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
    
    /**
     * Get the designation of the doctor.
     */
    public function designationInfo()
    {
        return $this->belongsTo(\App\Models\Designation::class, 'designation', 'designation_code');
    }

    /**
     * Get the doctor's user account.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'doctor_id');
    }

    /**
     * Get the doctor's name from the user relationship.
     */
    public function getNameAttribute()
    {
        return $this->user ? $this->user->name : 'N/A';
    }

    /**
     * Get consultations performed by this doctor
     */
    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'doctor_id', 'doctor_id');
    }

    /**
     * Get prescriptions written by this doctor
     */
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'doctor_id', 'doctor_id');
    }

    /**
     * Get lab orders made by this doctor
     */
    public function labOrders()
    {
        return $this->hasMany(Investigation::class, 'doctor_id', 'doctor_id');
    }

    /**
     * Return all active  Doctors
     */
    protected function active(){
        return $this->where('status', 1);
    }

}
