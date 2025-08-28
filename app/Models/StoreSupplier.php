<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreSupplier extends Model
{
    use HasFactory;

    protected $table = 'store_suppliers';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'postal_code',
        'tax_number',
        'license_number',
        'credit_limit',
        'credit_days',
        'payment_terms',
        'is_active'
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'credit_days' => 'integer',
        'is_active' => 'boolean'
    ];

    // Relationships
    
    /**
     * Get goods received notes from this supplier
     */
    public function goodsReceivedNotes()
    {
        return $this->hasMany(GoodsReceivedNote::class, 'supplier_id');
    }

    /**
     * Get medications supplied by this supplier
     */
    public function medications()
    {
        return $this->belongsToMany(Medication::class, 'goods_received_note_items', 'supplier_id', 'item_id')
                    ->where('item_type', 'medication');
    }

    // Scopes
    
    /**
     * Scope for active suppliers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for suppliers with credit limit exceeded
     */
    public function scopeCreditExceeded($query)
    {
        return $query->whereRaw('(SELECT SUM(total_amount) FROM goods_received_notes WHERE supplier_id = store_suppliers.id AND status = "pending") > credit_limit');
    }

    // Methods
    
    /**
     * Get total pending amount for this supplier
     */
    public function getPendingAmount()
    {
        return $this->goodsReceivedNotes()
                    ->where('status', 'pending')
                    ->sum('total_amount');
    }

    /**
     * Check if credit limit is exceeded
     */
    public function isCreditExceeded()
    {
        return $this->getPendingAmount() > $this->credit_limit;
    }

    /**
     * Get full address
     */
    public function getFullAddressAttribute()
    {
        $address = $this->address;
        if ($this->city) $address .= ', ' . $this->city;
        if ($this->country) $address .= ', ' . $this->country;
        if ($this->postal_code) $address .= ' ' . $this->postal_code;
        
        return $address;
    }

    /**
     * Get display name with ID
     */
    public function getDisplayNameAttribute()
    {
        return $this->name . ' (#' . $this->id . ')';
    }
}
