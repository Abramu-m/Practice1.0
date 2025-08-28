<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    protected $table = 'prescriptions';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'patient_id',
        'consultation_id',
        'doctor_id',
        'medication_id',
        'dosage',
        'administration_route_id',
        'frequency_id',
        'duration_days',
        'quantity',
        'quantity_dispensed',
        'batches_used',
        'dispensing_type',
        'unit_price',
        'total_price',
        'instructions',
        'notes',
        'pharmacist_notes',
        'status',
        'is_paid',
        'is_discount',
        'discount_percent',
        'payment_method',
        'amount_paid',
        'paid_at',
        'paid_by',
        'prescribed_at',
        'dispensed_at',
        'dispensed_by',
        'reviewed_at',
        'reviewed_by'
    ];

    protected $casts = [
        'duration_days' => 'integer',
        'quantity' => 'decimal:2',
        'quantity_dispensed' => 'decimal:2',
        'batches_used' => 'array',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'is_paid' => 'boolean',
        'is_discount' => 'boolean',
        'status' => 'string',
        'prescribed_at' => 'datetime',
        'dispensed_at' => 'datetime',
        'paid_at' => 'datetime',
        'reviewed_at' => 'datetime'
    ];

    // Status constants (updated structure)
    const STATUS_DRAFT = 'draft';
    const STATUS_PRESCRIBED = 'prescribed';
    const STATUS_PREPARED = 'prepared';
    const STATUS_DISPENSED = 'dispensed';
    const STATUS_UNAVAILABLE = 'unavailable';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the patient that owns the prescription
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    /**
     * Get the consultation for this prescription
     */
    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    /**
     * Get the doctor who prescribed
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get the doctor who prescribed (alias for doctor relationship)
     */
    public function doctorInfo()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    // Backwards-compatible alias: allow $prescription->doctorInfo() and $prescription->doctor()
    // to be used interchangeably by templates/controllers during transition.
    public function doctorAlias()
    {
        return $this->doctorInfo();
    }

    /**
     * Get the medication prescribed
     */
    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }

    /**
     * Get the route of administration
     */
    public function administrationRoute()
    {
        return $this->belongsTo(AdministrationRoute::class);
    }

    /**
     * Get the frequency
     */
    public function frequency()
    {
        return $this->belongsTo(MedicationFrequency::class);
    }

    /**
     * Get the user who dispensed this prescription
     */
    public function dispensedBy()
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }

    /**
     * Get prescription status options
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PRESCRIBED => 'Prescribed',
            self::STATUS_PREPARED => 'Prepared',
            self::STATUS_DISPENSED => 'Dispensed',
            self::STATUS_CANCELLED => 'Cancelled'
        ];
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $statuses = self::getStatusOptions();
        return $statuses[$this->status] ?? 'Unknown';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case self::STATUS_DRAFT: return 'badge-secondary';
            case self::STATUS_PRESCRIBED: return 'badge-info';
            case self::STATUS_PREPARED: return 'badge-warning';
            case self::STATUS_DISPENSED: return 'badge-success';
            case self::STATUS_CANCELLED: return 'badge-danger';
            default: return 'badge-secondary';
        }
    }

    /**
     * Calculate total price automatically
     */
    protected static function booted()
    {
        static::saving(function ($prescription) {
            $prescription->total_price = floatval($prescription->quantity) * floatval($prescription->unit_price);
        });
    }

    /**
     * Scope for active prescriptions
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', self::STATUS_CANCELLED);
    }

    /**
     * Scope for pending prescriptions
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_DRAFT, self::STATUS_PRESCRIBED, self::STATUS_PREPARED]);
    }

    // Dispensing type constants
    const DISPENSING_TYPE_INDIVIDUAL = 'individual';
    const DISPENSING_TYPE_BATCH = 'batch';

    /**
     * Add a batch to the batches_used array
     */
    public function addBatch($batchNumber, $quantity, $expiryDate, $locationId, $locationName = null)
    {
        $batches = $this->batches_used ?? [];
        
        $batches[] = [
            'batch_number' => $batchNumber,
            'quantity' => number_format($quantity, 2, '.', ''),
            'expiry_date' => $expiryDate,
            'location_id' => $locationId,
            'location_name' => $locationName
        ];
        
        $this->batches_used = $batches;
        return $this;
    }

    /**
     * Get total quantity from all batches
     */
    public function getTotalBatchQuantityAttribute()
    {
        if (!$this->batches_used) {
            return 0;
        }
        
        return array_sum(array_column($this->batches_used, 'quantity'));
    }

    /**
     * Get batch information formatted for display
     */
    public function getBatchInfoAttribute()
    {
        if (!$this->batches_used) {
            return [];
        }
        
        return collect($this->batches_used)->map(function ($batch) {
            return [
                'batch_number' => $batch['batch_number'],
                'quantity' => $batch['quantity'],
                'expiry_date' => $batch['expiry_date'],
                'location_id' => $batch['location_id'] ?? null,
                'location_name' => $batch['location_name'] ?? 'Unknown Location',
                'is_expired' => isset($batch['expiry_date']) && now() > $batch['expiry_date']
            ];
        })->toArray();
    }

    /**
     * Check if prescription has expired batches
     */
    public function hasExpiredBatches()
    {
        if (!$this->batches_used) {
            return false;
        }
        
        foreach ($this->batches_used as $batch) {
            if (isset($batch['expiry_date']) && now() > $batch['expiry_date']) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get remaining quantity to dispense
     */
    public function getRemainingQuantityAttribute()
    {
        return $this->quantity - ($this->quantity_dispensed ?? 0);
    }
}
