<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralHospital extends Model
{
    use HasFactory;

    protected $table = 'referral_hospitals';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'website',
        'notes',
        'is_active',
    ];

    public function departments()
    {
        return $this->hasMany(ReferralDepartment::class, 'referral_hospital_id', 'id');
    }
}
