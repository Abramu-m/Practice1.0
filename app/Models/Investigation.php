<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Investigation extends Model
{
    use HasFactory;

    protected $table = 'investigations';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'patient_id',
        'consultation_id',
        'visit_id', // Add visit_id to fillable
        'doctor_id',
        'medical_service_id',
        'quantity',
        'unit_price',
        'total_price',
        'insurance_covered_amount',
        'notes',
        'clinical_data',
        'priority',
        'status',
        'is_paid',
        'is_discount',
        'discount_percent',
        'payment_method',
        'amount_paid',
        'paid_at',
        'paid_by',
        'ordered_at',
        'collected_at',
        'resulted_at',
        'cancelled_at',
        'ordered_by',
        'collected_by',
        'resulted_by',
        'cancelled_by',
        'batches_used' // Add batches_used to fillable
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'insurance_covered_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'is_paid' => 'boolean',
        'is_discount' => 'boolean',
        'priority' => 'string',
        'status' => 'string',
        'clinical_data' => 'array',
        'batches_used' => 'array', // Cast batches_used as JSON array
        'ordered_at' => 'datetime',
        'collected_at' => 'datetime',
        'resulted_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'paid_at' => 'datetime'
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_ORDERED = 'ordered';
    const STATUS_COLLECTED = 'collected';
    const STATUS_PROCESSING = 'processing';
    const STATUS_RESULTED = 'resulted';
    const STATUS_CANCELLED = 'cancelled';

    // Priority constants
    const PRIORITY_ROUTINE = 'routine';
    const PRIORITY_URGENT = 'urgent';
    const PRIORITY_STAT = 'stat';

    /**
     * Get the patient that owns the investigation
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    /**
     * Get the consultation for this investigation
     */
    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    /**
     * Get the visit for this investigation
     */
    public function visit()
    {
        return $this->belongsTo(\App\Models\PatientVisit::class, 'visit_id', 'id');
    }

    /**
     * Get the doctor who ordered the investigation
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }

    /**
     * Get the medical service/test ordered
     */
    public function medicalService()
    {
        return $this->belongsTo(MedicalService::class);
    }

    /**
     * Get template-based investigation results
     */
    public function templateResults()
    {
        return $this->hasMany(InvestigationTemplateResult::class);
    }

    /**
     * Get investigation results (alias for template results)
     */
    public function results()
    {
        return $this->templateResults();
    }

    /**
     * Get the user who ordered the investigation
     */
    public function orderedBy()
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }

    /**
     * Get the user who collected the sample
     */
    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    /**
     * Get the user who resulted the investigation
     */
    public function resultedBy()
    {
        return $this->belongsTo(User::class, 'resulted_by');
    }

    /**
     * Get investigation consumptions
     */
    public function investigationConsumptions()
    {
        return $this->hasMany(InvestigationConsumption::class);
    }

    /**
     * Get investigation consumables (requirements)
     */
    public function investigationConsumables()
    {
        return $this->hasMany(InvestigationConsumable::class);
    }

    /**
     * Get investigation status options
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ORDERED => 'Ordered',
            self::STATUS_COLLECTED => 'Sample Collected',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_RESULTED => 'Results Available',
            self::STATUS_CANCELLED => 'Cancelled'
        ];
    }

    /**
     * Get status order for progression comparisons
     */
    public static function getStatusOrder()
    {
        return [
            self::STATUS_DRAFT => 1,
            self::STATUS_ORDERED => 2,
            self::STATUS_COLLECTED => 3,
            self::STATUS_PROCESSING => 4,
            self::STATUS_RESULTED => 5,
            self::STATUS_CANCELLED => 6,
        ];
    }

    /**
     * Get priority options
     */
    public static function getPriorityOptions()
    {
        return [
            self::PRIORITY_ROUTINE => 'Routine',
            self::PRIORITY_URGENT => 'Urgent',
            self::PRIORITY_STAT => 'STAT'
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
     * Check if investigation can be deleted (up to ordered status)
     */
    public function canBeDeleted()
    {
        $order = self::getStatusOrder();
        if (!isset($order[$this->status])) {
            return false;
        }

        return $order[$this->status] <= $order[self::STATUS_ORDERED];
    }

    /**
     * Get priority label
     */
    public function getPriorityLabelAttribute()
    {
        $priorities = self::getPriorityOptions();
        return $priorities[$this->priority] ?? 'Routine';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case self::STATUS_DRAFT: return 'bg-secondary';
            case self::STATUS_ORDERED: return 'bg-warning';
            case self::STATUS_COLLECTED: return 'bg-primary';
            case self::STATUS_PROCESSING: return 'bg-info';
            case self::STATUS_RESULTED: return 'bg-success';
            case self::STATUS_CANCELLED: return 'bg-danger';
            default: return 'bg-secondary';
        }
    }

    /**
     * Get priority badge class
     */
    public function getPriorityBadgeClassAttribute()
    {
        switch ($this->priority) {
            case self::PRIORITY_STAT: return 'bg-danger';
            case self::PRIORITY_URGENT: return 'bg-warning';
            case self::PRIORITY_ROUTINE: return 'bg-info';
            default: return 'bg-secondary';
        }
    }

    /**
     * Calculate total price automatically
     */
    protected static function booted()
    {
        static::saving(function ($investigation) {
            $investigation->total_price = floatval($investigation->quantity) * floatval($investigation->unit_price);
        });
    }

    /**
     * Scope for active investigations
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', self::STATUS_CANCELLED);
    }

    /**
     * Scope for pending investigations
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_ORDERED, self::STATUS_COLLECTED]);
    }

    /**
     * Scope for completed investigations
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_RESULTED);
    }

    /**
     * Scope for urgent investigations
     */
    public function scopeUrgent($query)
    {
        return $query->whereIn('priority', [self::PRIORITY_URGENT, self::PRIORITY_STAT]);
    }

    /**
     * Scope for paid investigations
     */
    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    /**
     * Scope for unpaid investigations
     */
    public function scopeUnpaid($query)
    {
        return $query->where('is_paid', false);
    }

    /**
     * Scope for investigations by priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for investigations by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if investigation requires sample collection
     */
    public function requiresSample()
    {
        return $this->medicalService ? $this->medicalService->requires_sample : false;
    }

    /**
     * Check if investigation is urgent
     */
    public function isUrgent()
    {
        return in_array($this->priority, [self::PRIORITY_URGENT, self::PRIORITY_STAT]);
    }

    /**
     * Check if investigation is completed
     */
    public function isCompleted()
    {
        return $this->status === self::STATUS_RESULTED;
    }

    /**
     * Check if investigation is cancelled
     */
    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Get the effective price after insurance coverage
     */
    public function getEffectivePriceAttribute()
    {
        return $this->total_price - ($this->insurance_covered_amount ?? 0);
    }

    /**
     * Get formatted total price
     */
    public function getFormattedTotalPriceAttribute()
    {
        return 'Tsh' . number_format($this->total_price, 2);
    }

    /**
     * Get formatted effective price
     */
    public function getFormattedEffectivePriceAttribute()
    {
        return 'Tsh' . number_format($this->effective_price, 2);
    }

    /**
     * Get investigation age in hours
     */
    public function getAgeInHoursAttribute()
    {
        if (!$this->ordered_at) return null;
        return $this->ordered_at->diffInHours(now());
    }

    /**
     * Get formatted age display
     */
    public function getFormattedAgeAttribute()
    {
        if (!$this->age_in_hours) return null;
        
        if ($this->age_in_hours < 1) {
            return round($this->age_in_hours * 60) . ' m ago';
        } elseif ($this->age_in_hours < 24) {
            return round($this->age_in_hours, 1) . ' h ago';
        } else {
            return round($this->age_in_hours / 24, 1) . ' d ago';
        }
    }

    /**
     * Get turnaround time for this investigation
     */
    public function getTurnaroundTimeAttribute()
    {
        if (!$this->ordered_at || !$this->resulted_at) return null;
        return $this->ordered_at->diffInHours($this->resulted_at);
    }

    /**
     * Check if investigation is overdue
     */
    public function isOverdue()
    {
        if (!$this->medicalService || $this->isCompleted() || $this->isCancelled()) {
            return false;
        }
        
        $expectedTurnaroundHours = $this->medicalService->turnaround_time_hours ?? 24;
        return $this->age_in_hours > $expectedTurnaroundHours;
    }
}
