<?php

namespace App\Services\CDS\Calculators;

class EgfrCalculator
{
    /**
     * Cockcroft-Gault (approx) — for illustration.
     */
    public static function cockcroftGault(float $age, float $weightKg, float $serumCreatinineMgDl, string $sex): float
    {
        $sexFactor = strtolower($sex) === 'female' ? 0.85 : 1.0;
        $egfr = (($sexFactor * (140 - $age) * $weightKg) / (72 * max($serumCreatinineMgDl, 0.01)));
        return round($egfr, 1);
    }
}
