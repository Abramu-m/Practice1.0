<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralDepartment extends Model
{
    use HasFactory;

    protected $table = 'referral_departments';

    protected $fillable = [
        'name',
        'description',
        'phone',
        'email',
        'is_active',
    ];
}
