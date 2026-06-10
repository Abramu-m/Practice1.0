<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParasitologyReportRow extends Model
{
    protected $fillable = [
        'row_key',
        'row_label',
        'sort_order',
        'service_ids',
        'param_name',
        'required_template_name',
        'positive_statuses',
        'shows_total',
        'shows_positive',
        'is_section_header',
        'is_configurable',
    ];

    protected $casts = [
        'service_ids'       => 'array',
        'positive_statuses' => 'array',
        'shows_total'       => 'boolean',
        'shows_positive'    => 'boolean',
        'is_section_header' => 'boolean',
        'is_configurable'   => 'boolean',
    ];
}
