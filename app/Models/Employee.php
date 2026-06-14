<?php

namespace App\Models;

use App\Models\Concerns\Syncable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Employee extends Model
{
    use Syncable;

    protected $fillable = [
        'employee_number',
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'phone',
        'email',
        'address',
        'job_title',
        'department',
        'employment_type',
        'date_joined',
        'basic_salary',
        'bank_name',
        'bank_account_number',
        'tin_number',
        'nssf_number',
        'payment_method',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_joined' => 'date',
        'basic_salary' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($employee) {
            if (empty($employee->employee_number)) {
                $employee->employee_number = self::generateEmployeeNumber();
            }

            if (empty($employee->created_by)) {
                $employee->created_by = Auth::id();
            }
        });
    }

    public static function generateEmployeeNumber(): string
    {
        $prefix = 'EMP';

        $lastEmployee = self::where('employee_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        $lastNumber = $lastEmployee
            ? (int) substr($lastEmployee->employee_number, strlen($prefix))
            : 0;

        return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function salaryComponents(): HasMany
    {
        return $this->hasMany(EmployeeSalaryComponent::class);
    }

    public function salaryPayments(): HasMany
    {
        return $this->hasMany(SalaryPayment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', '!=', 'active');
    }

    public function getNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-success',
            'inactive' => 'bg-secondary',
            'terminated' => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
