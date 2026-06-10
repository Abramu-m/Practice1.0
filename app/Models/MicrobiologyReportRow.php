<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MicrobiologyReportRow extends Model
{
    protected $fillable = [
        'row_key',
        'row_label',
        'sort_order',
        'service_ids',
        'show_total',
        'show_positive',
        'required_template_name',
        'param_name',
        'match_field',
        'match_values',
        'json_path',
        'json_path_values',
        'is_bold',
        'include_in_grand_total',
        'is_configurable',
    ];

    protected $casts = [
        'service_ids'            => 'array',
        'match_values'           => 'array',
        'json_path_values'       => 'array',
        'show_total'             => 'boolean',
        'show_positive'          => 'boolean',
        'is_bold'                => 'boolean',
        'include_in_grand_total' => 'boolean',
        'is_configurable'        => 'boolean',
    ];
}
