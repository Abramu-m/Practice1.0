<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StoreDispensingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'dispensing_id',
        'item_type',
        'item_id',
        'batch_id',
        'quantity_dispensed',
        'unit_price',
        'total_price',
        'notes',
    ];

    protected $casts = [
        'quantity_dispensed' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function dispensing(): BelongsTo
    {
        return $this->belongsTo(StoreDispensing::class, 'dispensing_id');
    }

    public function item(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForConsumable($query, $consumableId)
    {
        return $query->where('item_type', 'consumable')
                    ->where('item_id', $consumableId);
    }

    public function scopeToday($query)
    {
        return $query->whereHas('dispensing', function ($q) {
            $q->whereDate('dispensing_date', today());
        });
    }

    public function scopeThisWeek($query)
    {
        return $query->whereHas('dispensing', function ($q) {
            $q->whereBetween('dispensing_date', [now()->startOfWeek(), now()->endOfWeek()]);
        });
    }

    public function scopeThisMonth($query)
    {
        return $query->whereHas('dispensing', function ($q) {
            $q->whereMonth('dispensing_date', now()->month)
              ->whereYear('dispensing_date', now()->year);
        });
    }

    public function calculateTotal(): float
    {
        return $this->quantity_dispensed * $this->unit_price;
    }

    public function getFormattedUnitPriceAttribute(): string
    {
        return number_format($this->unit_price, 2);
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        return number_format($this->total_price, 2);
    }

    public function getItemNameAttribute(): string
    {
        return $this->item?->name ?? 'Unknown Item';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (!$item->total_price) {
                $item->total_price = $item->quantity_dispensed * $item->unit_price;
            }
        });

        static::updating(function ($item) {
            if ($item->isDirty(['quantity_dispensed', 'unit_price'])) {
                $item->total_price = $item->quantity_dispensed * $item->unit_price;
            }
        });
    }
}
