<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsReceivedNote extends Model
{
    use HasFactory;

    protected $table = 'goods_received_notes';

    protected $fillable = [
        'grn_number',
        'grn_date',
        'supplier_id',
        'invoice_number',
        'invoice_date',
        'delivery_note_number',
        'delivery_date',
        'total_amount',
        'discount_amount',
        'tax_amount',
        'net_amount',
        'status',
        'notes',
        'received_by',
        'received_at',
        'verified_by',
        'verified_at',
        'posted_by',
        'posted_at',
    ];

    protected $casts = [
        'grn_date' => 'date',
        'invoice_date' => 'date',
        'delivery_date' => 'date',
        'received_at' => 'datetime',
        'verified_at' => 'datetime',
        'posted_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'net_amount' => 'decimal:2'
    ];

    // Relationships
    
    /**
     * Get the supplier
     */
    public function supplier()
    {
        return $this->belongsTo(StoreSupplier::class, 'supplier_id');
    }

    /**
     * Get the user who received the goods
     */
    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Get the user who verified the goods
     */
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the user who posted the goods
     */
    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /**
     * Get the user who approved the GRN
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get GRN items directly
     */
    public function items()
    {
        return $this->hasMany(GoodsReceivedNoteItem::class, 'grn_id');
    }

    /**
     * Get medication items only
     */
    public function medicationItems()
    {
        return $this->items()->medications();

    }

    /**
     * Get consumable items only
     */
    public function consumableItems()
    {
        return $this->items()->consumables();
    }

    // Scopes
    
    /**
     * Scope for draft GRNs
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope for received GRNs
     */
    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    /**
     * Scope for verified GRNs
     */
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    /**
     * Scope for posted GRNs
     */
    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    /**
     * Scope for cancelled GRNs
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope for GRNs by supplier
     */
    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    // Methods
    
    /**
     * Generate GRN number
     */
    public static function generateGrnNumber()
    {
        $lastGrn = static::latest()->first();
        $nextId = $lastGrn ? ($lastGrn->id + 1) : 1;
        
        return 'GRN' . date('Y') . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate total amount from stock batches
     */
    public function calculateTotalAmount()
    {
        $this->total_amount = $this->items()->sum('total_cost');
        $this->discount_amount = $this->items()->sum('discount_amount');
        $this->tax_amount = $this->items()->sum('tax_amount');
        $this->net_amount = $this->total_amount + $this->tax_amount - $this->discount_amount;
        $this->save();
        
        return $this->total_amount;
    }

    /**
     * Reject GRN (change to cancelled status)
     */
    public function reject($reason = null)
    {
        $this->status = 'cancelled';
        if ($reason) {
            $this->notes = $this->notes ? $this->notes . "\n\nReason: " . $reason : "Reason: " . $reason;
        }
        $this->save();
        
        return $this;
    }

    /**
     * Mark as verified
     */
    public function markAsVerified()
    {
        $this->status = 'verified';
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
     * Get total quantity received
     */
    public function getTotalQuantityAttribute()
    {
        return $this->items()->sum('received_quantity');
    }

    /**
     * Get formatted invoice date
     */
    public function getFormattedInvoiceDateAttribute()
    {
        return $this->invoice_date ? $this->invoice_date->format('d/m/Y') : 'N/A';
    }

    /**
     * Get formatted delivery date
     */
    public function getFormattedDeliveryDateAttribute()
    {
        return $this->delivery_date ? $this->delivery_date->format('d/m/Y') : 'N/A';
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case 'posted':
                return 'success';
            case 'verified':
                return 'info';
            case 'received':
                return 'primary';
            case 'cancelled':
                return 'danger';
            case 'draft':
            default:
                return 'warning';
        }
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute()
    {
        switch ($this->status) {
            case 'posted':
                return 'bg-success';
            case 'verified':
                return 'bg-info';
            case 'received':
                return 'bg-primary';
            case 'cancelled':
                return 'bg-danger';
            case 'draft':
            default:
                return 'bg-warning';
        }
    }
}
