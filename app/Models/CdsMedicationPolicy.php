<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class CdsMedicationPolicy extends Model
{
    protected $fillable = [
        'medication_name', 'generic_names', 'brand_names', 
        'therapeutic_class', 'is_active', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'generic_names' => 'array',
        'brand_names' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }

    public function dosageLimits(): HasMany
    {
        return $this->hasMany(CdsDosageLimit::class, 'medication_policy_id');
    }

    public function activeDosageLimits(): HasMany
    {
        return $this->dosageLimits()->where('is_active', true);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper method to match medication name
    public static function findByMedicationName(string $medicationName): ?self
    {
        $name = strtolower(trim($medicationName));
        
        return static::active()
            ->where(function ($query) use ($name) {
                $query->whereRaw('LOWER(medication_name) LIKE ?', ["%{$name}%"])
                      ->orWhereRaw('JSON_SEARCH(LOWER(generic_names), "one", ?) IS NOT NULL', ["%{$name}%"])
                      ->orWhereRaw('JSON_SEARCH(LOWER(brand_names), "one", ?) IS NOT NULL', ["%{$name}%"]);
            })
            ->first();
    }

    // Get all names (medication + generic + brand)
    public function getAllNames(): array
    {
        $names = [$this->medication_name];
        
        if ($this->generic_names) {
            $names = array_merge($names, $this->generic_names);
        }
        
        if ($this->brand_names) {
            $names = array_merge($names, $this->brand_names);
        }
        
        return array_unique(array_filter($names));
    }
}
