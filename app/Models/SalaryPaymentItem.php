<?php

namespace App\Models;

use App\Models\Concerns\Syncable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryPaymentItem extends Model
{
    use Syncable;

    protected $fillable = [
        'salary_payment_id',
        'type',
        'name',
        'amount',
        'is_taxable',
        'is_pre_tax',
        'is_statutory',
        'source_component_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_taxable' => 'boolean',
        'is_pre_tax' => 'boolean',
        'is_statutory' => 'boolean',
    ];

    public function salaryPayment(): BelongsTo
    {
        return $this->belongsTo(SalaryPayment::class);
    }

    public function sourceComponent(): BelongsTo
    {
        return $this->belongsTo(EmployeeSalaryComponent::class, 'source_component_id');
    }

    public function scopeAllowances($query)
    {
        return $query->where('type', 'allowance');
    }

    public function scopeDeductions($query)
    {
        return $query->where('type', 'deduction');
    }

    public function scopeStatutory($query)
    {
        return $query->where('is_statutory', true);
    }
}
