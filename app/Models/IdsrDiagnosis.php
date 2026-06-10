<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdsrDiagnosis extends Model
{
    protected $fillable = ['name'];

    public function icdMappings()
    {
        return $this->hasMany(IdsrIcdMapping::class);
    }
}
