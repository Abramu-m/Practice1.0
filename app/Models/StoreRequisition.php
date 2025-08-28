<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class StoreRequisition extends Model
{
    use HasFactory;

    protected $table = 'store_requisitions';

    protected $fillable = [
        'requisition_number',
        'requisition_date',
        'requesting_location_id',
        'issuing_location_id',
        'priority',
        'required_date',
        'purpose',
        'total_estimated_cost',
        'status',
        'notes',
        'rejection_reason',
        'requested_by',
        'approved_by',
        'approved_at',
        'issued_by',
        'issued_at'
    ];

    protected $casts = [
        'requisition_date' => 'date',
        'required_date' => 'date',
        'approved_at' => 'datetime',
        'issued_at' => 'datetime',
        'total_estimated_cost' => 'decimal:2'
    ];

    // Relationships
    
    /**
     * Get the requesting location
     */
    public function requestingLocation()
    {
        return $this->belongsTo(StoreLocation::class, 'requesting_location_id');
    }

    /**
     * Get the issuing location
     */
    public function issuingLocation()
    {
        return $this->belongsTo(StoreLocation::class, 'issuing_location_id');
    }

    /**
     * Get the user who requested
     */
    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who approved
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who issued
     */
    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * Get requisition items
     */
    public function items()
    {
        return $this->hasMany(StoreRequisitionItem::class, 'requisition_id');
    }

    /**
     * Get medication items
     */
    public function medicationItems()
    {
        return $this->items()->where('item_type', 'medication');
    }

    /**
     * Get consumable items
     */
    public function consumableItems()
    {
        return $this->items()->where('item_type', 'consumable');
    }

    // Scopes
    
    /**
     * Scope for pending requisitions (draft or submitted)
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'submitted']);
    }

    /**
     * Scope for approved requisitions
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for issued requisitions
     */
    public function scopeIssued($query)
    {
        return $query->whereIn('status', ['fully_issued', 'partially_issued']);
    }

    /**
     * Scope for rejected requisitions
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope for high priority requisitions
     */
    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    /**
     * Scope for requisitions by location
     */
    public function scopeByLocation($query, $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    /**
     * Scope for requisitions by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('requested_by', $userId);
    }

    // Methods
    
    /**
     * Generate requisition number
     */
    public static function generateRequisitionNumber()
    {
        $lastRequisition = static::latest()->first();
        $nextId = $lastRequisition ? ($lastRequisition->id + 1) : 1;
        
        return 'REQ' . date('Y') . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Approve requisition
     */
    public function approve($approvedBy = null)
    {
        $this->status = 'approved';
        $this->approved_by = $approvedBy ?: (Auth::check() ? Auth::user()->id : null);
        $this->approved_date = now();
        $this->save();
        
        return $this;
    }

    /**
     * Reject requisition
     */
    public function reject($reason = null)
    {
        $this->status = 'rejected';
        if ($reason) {
            $this->remarks = $reason;
        }
        $this->save();
        
        return $this;
    }

    /**
     * Mark as issued
     */
    public function markAsIssued($issuedBy = null)
    {
        $this->status = 'fully_issued';
        $this->issued_by = $issuedBy ?: (Auth::check() ? Auth::user()->id : null);
        $this->issued_date = now();
        $this->save();
        
        return $this;
    }

    /**
     * Get total items count
     */
    public function getTotalItemsAttribute()
    {
        return $this->items()->count();
    }

    /**
     * Get total requested quantity
     */
    public function getTotalRequestedQuantityAttribute()
    {
        return $this->items()->sum('requested_quantity');
    }

    /**
     * Get total approved quantity
     */
    public function getTotalApprovedQuantityAttribute()
    {
        return $this->items()->sum('approved_quantity');
    }

    /**
     * Get total issued quantity
     */
    public function getTotalIssuedQuantityAttribute()
    {
        return $this->items()->sum('issued_quantity');
    }

    /**
     * Get total cost
     */
    public function getTotalCostAttribute()
    {
        return $this->items()->sum('total_cost');
    }

    /**
     * Check if requisition is fully issued
     */
    public function isFullyIssued()
    {
        return $this->items()->where('approved_quantity', '>', 0)->count() > 0 &&
               $this->items()->whereRaw('issued_quantity < approved_quantity')->count() === 0;
    }

    /**
     * Check if requisition is partially issued
     */
    public function isPartiallyIssued()
    {
        return $this->items()->where('issued_quantity', '>', 0)->count() > 0 &&
               $this->items()->whereRaw('issued_quantity < approved_quantity')->count() > 0;
    }

    /**
     * Update status based on item statuses
     */
    public function updateStatus()
    {
        if ($this->status === 'approved') {
            if ($this->isFullyIssued()) {
                $this->status = 'fully_issued';
                $this->issued_date = now();
            } elseif ($this->isPartiallyIssued()) {
                $this->status = 'partially_issued';
            }
            
            $this->save();
        }
    }

    /**
     * Get formatted request date
     */
    public function getFormattedRequestDateAttribute()
    {
        return $this->request_date ? $this->request_date->format('d/m/Y') : 'N/A';
    }

    /**
     * Get formatted approved date
     */
    public function getFormattedApprovedDateAttribute()
    {
        return $this->approved_date ? $this->approved_date->format('d/m/Y') : 'N/A';
    }

    /**
     * Get formatted issued date
     */
    public function getFormattedIssuedDateAttribute()
    {
        return $this->issued_date ? $this->issued_date->format('d/m/Y') : 'N/A';
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case 'approved':
                return 'success';
            case 'fully_issued':
                return 'info';
            case 'partially_issued':
                return 'warning';
            case 'rejected':
                return 'danger';
            case 'cancelled':
                return 'danger';
            case 'submitted':
                return 'info';
            case 'draft':
            default:
                return 'secondary';
        }
    }

    /**
     * Get priority color
     */
    public function getPriorityColorAttribute()
    {
        switch ($this->priority) {
            case 'high':
                return 'danger';
            case 'medium':
                return 'warning';
            case 'low':
            default:
                return 'info';
        }
    }
}
