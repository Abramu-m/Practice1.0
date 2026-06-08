<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestigationFormData extends Model
{
    protected $table = 'investigation_form_data';

    protected $fillable = [
        'investigation_id',
        'form_data',
    ];

    protected $casts = [
        'form_data' => 'array', // automatically decode JSON
    ];

    public function investigation()
    {
        return $this->belongsTo(Investigation::class);
    }
}