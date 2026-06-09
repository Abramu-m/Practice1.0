<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HematologyReportRow extends Model
{
    protected $fillable = [
        'row_key', 'row_label', 'sort_order',
        'service_ids', 'fbp_param_name', 'required_template_name',
        'positive_results_only', 'track_low_high', 'is_section_header',
    ];

    protected $casts = [
        'service_ids'          => 'array',
        'positive_results_only' => 'boolean',
        'track_low_high'       => 'boolean',
        'is_section_header'    => 'boolean',
    ];
}
