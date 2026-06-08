<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CdsAlertAction extends Model
{
    use HasFactory;

    protected $table = 'cds_alert_actions';

    protected $fillable = [
        'cds_alert_id',
        'action',
        'reason',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function alert()
    {
        return $this->belongsTo(CdsAlert::class, 'cds_alert_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
