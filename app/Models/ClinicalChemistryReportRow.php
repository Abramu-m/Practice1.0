<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicalChemistryReportRow extends Model
{
    protected $fillable = [
        'row_key',
        'row_label',
        'sort_order',
        'service_ids',
        'param_name',
        'required_template_name',
        'abnormal_as_high',
        'track_low_high',
        'is_section_header',
        'is_configurable',
    ];

    protected $casts = [
        'service_ids'       => 'array',
        'abnormal_as_high'  => 'boolean',
        'track_low_high'    => 'boolean',
        'is_section_header' => 'boolean',
        'is_configurable'   => 'boolean',
    ];
}
