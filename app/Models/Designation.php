<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    protected $fillable = [
        'designation_code',
        'description',
        'status'
    ];

    /**
     * Get the doctors associated with the designation.
     */
    public function doctors()
    {
        return $this->hasMany('App\Models\Doctor', 'designation', 'designation_code');
    }

    /**
     * Get the active status of the designation.
     */
    public function isActive()
    {
        return $this->status === 1;
    }
}
