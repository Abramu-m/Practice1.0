<?php

namespace App\Models;

use App\Models\Concerns\Syncable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class EmployeeSalaryComponent extends Model
{
    use Syncable;

    protected $fillable = [
        'employee_id',
        'type',
        'name',
        'calculation_type',
        'amount',
        'percentage',
        'is_taxable',
        'is_pre_tax',
        'is_statutory',
        'is_active',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'is_taxable' => 'boolean',
        'is_pre_tax' => 'boolean',
        'is_statutory' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($component) {
            if (empty($component->created_by)) {
                $component->created_by = Auth::id();
            }
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Resolve the line-item amount for a given basic salary, depending on
     * whether this component is a fixed amount or a percentage of basic.
     */
    public function resolveAmount(float $basicSalary): float
    {
        if ($this->calculation_type === 'percentage_of_basic') {
            return round($basicSalary * ((float) $this->percentage / 100), 2);
        }

        return (float) $this->amount;
    }
}
