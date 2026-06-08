<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralDepartment extends Model
{
    use HasFactory;

    protected $table = 'referral_departments';

    protected $fillable = [
        'referral_hospital_id',
        'name',
        'description',
        'phone',
        'email',
        'is_active',
    ];

    public function hospital()
    {
        return $this->belongsTo(ReferralHospital::class, 'referral_hospital_id', 'id');
    }
}
