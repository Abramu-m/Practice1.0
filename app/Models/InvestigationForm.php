<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestigationForm extends Model
{
    protected $table = 'investigation_forms';

    protected $fillable = [
        'name',
        'description',
        'blade_view',
    ];
}
