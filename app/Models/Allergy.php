<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Allergy extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'medication_id',
        'substance_name',
        'reaction',
        'severity',
        'is_active',
        'recorded_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'recorded_at' => 'datetime',
    ];
}
