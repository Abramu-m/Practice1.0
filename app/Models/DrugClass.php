<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DrugClass extends Model
{
    protected $fillable = ['name', 'slug', 'category', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function medications()
    {
        return $this->belongsToMany(Medication::class, 'drug_class_medication');
    }
}
