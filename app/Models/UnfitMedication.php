<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnfitMedication extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'medication_id',
        'source_type',
        'source_id',
        'batch_number',
        'expiry_date',
        'quantity_discarded',
        'reason',
        'disposal_method',
        'disposed_by',
        'disposed_at',
        'notes',
        'verification_required',
        'verified_by',
        'verified_at'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'disposed_at' => 'datetime',
        'verified_at' => 'datetime',
        'verification_required' => 'boolean',
        'quantity_discarded' => 'decimal:2',
    ];

    // Source type constants
    const SOURCE_LEDGER = 'ledger';
    const SOURCE_LOCATION_STOCK = 'location_stock';

    // Reason constants
    const REASON_EXPIRED = 'expired';
    const REASON_DAMAGED = 'damaged';
    const REASON_RECALLED = 'recalled';
    const REASON_CONTAMINATED = 'contaminated';
    const REASON_OTHER = 'other';

    // Disposal method constants
    const DISPOSAL_INCINERATION = 'incineration';
    const DISPOSAL_RETURN_SUPPLIER = 'return_supplier';
    const DISPOSAL_SECURE_DISPOSAL = 'secure_disposal';
    const DISPOSAL_OTHER = 'other';

    // Relationships

    /**
     * Get the medication
     */
    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }

    /**
     * Get the user who disposed the medication
     */
    public function disposedBy()
    {
        return $this->belongsTo(User::class, 'disposed_by');
    }

    /**
     * Get the user who verified the disposal
     */
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the source record (polymorphic relationship)
     */
    public function source()
    {
        switch ($this->source_type) {
            case self::SOURCE_LEDGER:
                return $this->belongsTo(MedicationLedger::class, 'source_id');
            case self::SOURCE_LOCATION_STOCK:
                return $this->belongsTo(StoreLocationStock::class, 'source_id');
            default:
                return null;
        }
    }

    // Scopes

    /**
     * Scope to get unverified disposals
     */
    public function scopeUnverified($query)
    {
        return $query->where('verification_required', true)
                    ->whereNull('verified_by');
    }

    /**
     * Scope to get verified disposals
     */
    public function scopeVerified($query)
    {
        return $query->where('verification_required', false)
                    ->orWhereNotNull('verified_by');
    }

    /**
     * Scope to filter by reason
     */
    public function scopeByReason($query, $reason)
    {
        return $query->where('reason', $reason);
    }

    /**
     * Scope to filter by disposal method
     */
    public function scopeByDisposalMethod($query, $method)
    {
        return $query->where('disposal_method', $method);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('disposed_at', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by medication
     */
    public function scopeByMedication($query, $medicationId)
    {
        return $query->where('medication_id', $medicationId);
    }

    /**
     * Scope to get pending verification
     */
    public function scopePendingVerification($query)
    {
        return $query->where('verification_required', true)
                    ->whereNull('verified_at');
    }

    // Accessor methods

    /**
     * Get reason display name
     */
    public function getReasonDisplayAttribute()
    {
        $reasons = [
            self::REASON_EXPIRED => 'Expired',
            self::REASON_DAMAGED => 'Damaged',
            self::REASON_RECALLED => 'Recalled',
            self::REASON_CONTAMINATED => 'Contaminated',
            self::REASON_OTHER => 'Other'
        ];

        return $reasons[$this->reason] ?? 'Unknown';
    }

    /**
     * Get disposal method display name
     */
    public function getDisposalMethodDisplayAttribute()
    {
        $methods = [
            self::DISPOSAL_INCINERATION => 'Incineration',
            self::DISPOSAL_RETURN_SUPPLIER => 'Return to Supplier',
            self::DISPOSAL_SECURE_DISPOSAL => 'Secure Disposal',
            self::DISPOSAL_OTHER => 'Other'
        ];

        return $methods[$this->disposal_method] ?? 'Unknown';
    }

    /**
     * Get verification status
     */
    public function getVerificationStatusAttribute()
    {
        if (!$this->verification_required) {
            return 'not_required';
        }

        if ($this->verified_at) {
            return 'verified';
        }

        return 'pending';
    }

    /**
     * Get verification status color for UI
     */
    public function getVerificationStatusColorAttribute()
    {
        switch ($this->verification_status) {
            case 'verified':
                return 'success';
            case 'pending':
                return 'warning';
            case 'not_required':
                return 'info';
            default:
                return 'secondary';
        }
    }

    /**
     * Get reason color for UI
     */
    public function getReasonColorAttribute()
    {
        switch ($this->reason) {
            case self::REASON_EXPIRED:
                return 'warning';
            case self::REASON_DAMAGED:
            case self::REASON_CONTAMINATED:
                return 'danger';
            case self::REASON_RECALLED:
                return 'info';
            default:
                return 'secondary';
        }
    }

    /**
     * Get disposal value (quantity × unit cost if available)
     */
    public function getDisposalValueAttribute()
    {
        $source = $this->source();
        if ($source) {
            // Try to get unit cost from the source
            $unitCost = null;
            if (method_exists($source, 'getAttribute')) {
                $unitCost = $source->getAttribute('unit_cost');
            }
            
            if ($unitCost) {
                return $this->quantity_discarded * $unitCost;
            }
        }
        
        return null;
    }

    // Helper methods

    /**
     * Check if disposal needs verification
     */
    public function needsVerification()
    {
        return $this->verification_required && !$this->verified_at;
    }

    /**
     * Check if disposal is verified
     */
    public function isVerified()
    {
        return !$this->verification_required || $this->verified_at;
    }

    /**
     * Verify the disposal
     */
    public function verify($verifiedBy)
    {
        $this->verified_by = $verifiedBy;
        $this->verified_at = now();
        
        return $this->save();
    }

    /**
     * Get all available reasons
     */
    public static function getReasons()
    {
        return [
            self::REASON_EXPIRED => 'Expired',
            self::REASON_DAMAGED => 'Damaged',
            self::REASON_RECALLED => 'Recalled',
            self::REASON_CONTAMINATED => 'Contaminated',
            self::REASON_OTHER => 'Other'
        ];
    }

    /**
     * Get all available disposal methods
     */
    public static function getDisposalMethods()
    {
        return [
            self::DISPOSAL_INCINERATION => 'Incineration',
            self::DISPOSAL_RETURN_SUPPLIER => 'Return to Supplier',
            self::DISPOSAL_SECURE_DISPOSAL => 'Secure Disposal',
            self::DISPOSAL_OTHER => 'Other'
        ];
    }

    /**
     * Create disposal record from source
     */
    public static function createFromSource($source, $quantity, $reason, $disposalMethod, $disposedBy, $notes = null)
    {
        $sourceType = $source instanceof MedicationLedger ? self::SOURCE_LEDGER : self::SOURCE_LOCATION_STOCK;
        
        return self::create([
            'medication_id' => $source->medication_id,
            'source_type' => $sourceType,
            'source_id' => $source->id,
            'batch_number' => $source->batch_number,
            'expiry_date' => $source->expiry_date,
            'quantity_discarded' => $quantity,
            'reason' => $reason,
            'disposal_method' => $disposalMethod,
            'disposed_by' => $disposedBy,
            'disposed_at' => now(),
            'notes' => $notes,
            'verification_required' => $quantity > 100 || $reason === self::REASON_RECALLED, // Business rule
        ]);
    }
}
