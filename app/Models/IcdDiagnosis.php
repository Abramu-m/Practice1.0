<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IcdDiagnosis extends Model
{
    protected $fillable = [
        'consultation_id',
        'icd_code',
        'description',
        'type',
        'category',
        'subcategory',
        'added_by',
    ];
    
    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }
    
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
    
    public function icd10()
    {
        return $this->belongsTo(Icd10::class, 'icd_code', 'code');
    }
}