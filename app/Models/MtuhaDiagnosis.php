<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MtuhaDiagnosis extends Model
{
    protected $table = 'mtuha_diagnoses';

    protected $fillable = [
        'description'
    ];

    public $timestamps = false;

    public function icd10s()
    {
        return $this->hasMany(Icd10::class, 'mtuha_diagnosis', 'id');
    }
}
