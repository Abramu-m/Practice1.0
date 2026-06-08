<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitType extends Model
{
    protected $fillable = ['description'];

    /**
     * Get the visits associated with the visit type.
     */
    public function visits()
    {
        return $this->hasMany('App\Models\Visit');
    }
}
