<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\Syncable;
use App\Models\Facility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use phpDocumentor\Reflection\PseudoTypes\False_;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, Syncable;

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
        'signature',
        'role',
        'is_admin',
        'is_super',
        'is_active',
        'email',
        'email_verified_at',
        'imap_password',
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
        'imap_password',
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
            'imap_password' => 'encrypted',
        ];
    }
    
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Scope a query to only users with admin or super-admin privileges.
     */
    public function scopeAdmins($query)
    {
        return $query->where(function ($q) {
            $q->where('is_admin', true)->orWhere('is_super', true);
        });
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

    /**
     * Check whether this user is allowed to view the work email feature:
     * their email must be verified and match the facility's email domain.
     */
    public function canAccessEmail(): bool
    {
        if (!$this->email || !$this->email_verified_at) {
            return false;
        }

        $domain = Facility::current()->email_domain;

        if (!$domain) {
            return false;
        }

        return str_ends_with(strtolower($this->email), '@' . strtolower($domain));
    }

    /**
     * Check whether this user has saved mailbox (IMAP) credentials.
     */
    public function hasMailboxConnected(): bool
    {
        return !empty($this->imap_password);
    }

    /**
     * Get the role-specific nav partial for an admin's underlying functional
     * role (e.g. doctor, nurse), or null if they have no functional role.
     */
    public function getFunctionalNavRole(): ?string
    {
        return match(true) {
            $this->isReceptionist() => 'receptionist',
            $this->isDoctor() => 'doctor',
            $this->isCashier() => 'receptionist',
            $this->isLabTechnician() => 'lab_technician',
            $this->isPharmacist() => 'pharmacist',
            $this->isNurse() => 'nurse',
            $this->isRadiologist() => 'radiologist',
            default => null,
        };
    }

}
