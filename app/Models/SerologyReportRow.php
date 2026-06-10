<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SerologyReportRow extends Model
{
    protected $fillable = [
        'row_key',
        'row_label',
        'sort_order',
        'service_ids',
        'required_template_name',
        'positive_statuses',
        'cd4_filter',
        'is_configurable',
    ];

    protected $casts = [
        'service_ids'       => 'array',
        'positive_statuses' => 'array',
        'is_configurable'   => 'boolean',
    ];
}
