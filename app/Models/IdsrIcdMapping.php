<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdsrIcdMapping extends Model
{
    protected $table = 'idsr_icd_mapping';

    protected $fillable = ['idsr_diagnosis_id', 'icd_code', 'status'];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function diagnosis()
    {
        return $this->belongsTo(IdsrDiagnosis::class, 'idsr_diagnosis_id');
    }
}
