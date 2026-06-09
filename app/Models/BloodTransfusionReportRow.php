<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BloodTransfusionReportRow extends Model
{
    protected $fillable = ['row_key', 'row_label', 'sort_order', 'service_ids'];

    protected $casts = ['service_ids' => 'array'];
}
