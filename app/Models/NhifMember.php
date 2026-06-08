<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NhifMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_no',
        'card_status',
        'first_name',
        'middle_name',
        'last_name',
        'full_name',
        'gender',
        'date_of_birth',
        'expiry_date',
        'authorization_status',
        'authorization_no',
        'employer_no',
        'scheme_id',
        'product_code',
        'remarks',
        'patient_id',
        'verification_date',
        'verified_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'expiry_date' => 'date',
        'verification_date' => 'datetime',
    ];

    /**
     * Relationship with Patient
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Relationship with User (who verified)
     */
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Check if member is active
     */
    public function isActive(): bool
    {
        return $this->card_status === 'Active';
    }

    //Authorization  takes data from authorization_status(Facility: ; Date: ; Status: ;)
   
    /**
     * Extract authorization details from authorization_status
     */
    public function getAuthorizationFacility(): ?string
    {
        if (!$this->authorization_status) {
            return null;
        }

        preg_match('/Facility:\s*([^;]+)/', $this->authorization_status, $matches);
        return $matches[1] ?? null;
    }

    /**
     * Extract authorization date from authorization_status
     */
    public function getAuthorizationDate(): ?string
    {
        if (!$this->authorization_status) {
            return null;
        }

        preg_match('/Date:\s*([^;]+)/', $this->authorization_status, $matches);
        return $matches[1] ?? null;
    }

    /**
     * Check if card is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        
        return $this->expiry_date->isPast();
    }
}
