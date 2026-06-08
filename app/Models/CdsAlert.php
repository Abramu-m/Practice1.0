<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CdsAlert extends Model
{
    use HasFactory;

    protected $table = 'cds_alerts';

    protected $fillable = [
        'patient_id',
        'visit_id',
        'subject_type',
        'subject_id',
        'rule_key',
        'rule_version',
        'severity',
        'message',
        'rationale',
        'payload',
        'status',
        'created_by',
        'resolved_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function actions()
    {
        return $this->hasMany(CdsAlertAction::class);
    }
}
