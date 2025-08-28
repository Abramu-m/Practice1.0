<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'username',
        'phone',
        'gender',
        'address',
        'profile_picture',
        'role',
        'is_admin',
        'is_super',
        'is_active',
        'email',
        'email_verified_at',
        'password',
        'is_verified',
        'verified_at',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'is_active' => 'boolean',
            'is_admin' => 'boolean',
            'is_super' => 'boolean',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }
    
    public function hasRole($role)
    {
        return $this->role === $role;
    }
    
    /**
     * Get the doctor profile associated with the user.
     */
    public function doctor()
    {
        return $this->hasOne(\App\Models\Doctor::class, 'doctor_id', 'id');
    }
    
    /**
     * Get the full name attribute.
     */
    public function getNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name);
    }

    /**
     * Check if User is Admin.
     */
    public function isAdmin()
    {
        return $this->is_admin || $this->is_super;
    }

    /**
     * Check if User is Doctor.
     */
    public function isDoctor()
    {
        return $this->role === 'doctor' && $this->doctor()->exists();
    }
    
    /**
     * Check if User is Super Admin.
     */
    public function isSuperAdmin()
    {
        return $this->is_super;
    }
    /**
     * Check if User is Receptionist.
     */
    public function isReceptionist()
    {
        return $this->role === 'receptionist';
    }
    /**
     * Check if User is Nurse.
     */
    public function isNurse()
    {
        return $this->role === 'nurse';
    }
    /**
     * Check if User is Cashier.
     */
    public function isCashier()
    {
        return $this->role === 'cashier';
    }
    /**
     * Check if User is Pharmacist.
     */
    public function isPharmacist()
    {
        return $this->role === 'pharmacist';
    }
    /**
     * Check if User is Lab Technician.
     */
    public function isLabTechnician()
    {
        return $this->role === 'lab_technician';
    }
    /**
     * Check if User is Radiologist.
     */
    public function isRadiologist()
    {
        return $this->role === 'radiologist';
    }
    
}
