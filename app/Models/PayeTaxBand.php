<?php

namespace App\Models;

use App\Models\Concerns\Syncable;
use Illuminate\Database\Eloquent\Model;

class PayeTaxBand extends Model
{
    use Syncable;

    protected $fillable = [
        'band_order',
        'min_income',
        'max_income',
        'rate',
        'is_active',
    ];

    protected $casts = [
        'min_income' => 'decimal:2',
        'max_income' => 'decimal:2',
        'rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Progressive PAYE calculation: for each active band, tax the portion of
     * taxable income that falls within that band at the band's rate.
     */
    public static function calculate(float $taxableIncome): float
    {
        $tax = 0.0;

        $bands = self::active()->orderBy('band_order')->get();

        foreach ($bands as $band) {
            $min = (float) $band->min_income;
            $max = $band->max_income !== null ? (float) $band->max_income : null;

            if ($taxableIncome <= $min) {
                continue;
            }

            $upper = $max !== null ? min($taxableIncome, $max) : $taxableIncome;
            $taxablePortion = $upper - $min;

            if ($taxablePortion > 0) {
                $tax += $taxablePortion * ((float) $band->rate / 100);
            }
        }

        return round($tax, 2);
    }
}
