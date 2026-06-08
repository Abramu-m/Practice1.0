<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NhifSetting extends Model
{
    protected $fillable = ['mode', 'username', 'password'];

    protected $casts = [
        'username' => 'encrypted',
        'password' => 'encrypted',
    ];

    /**
     * Always return the single NHIF settings record, or a default stub if none exists.
     */
    public static function current(): static
    {
        return static::firstOrNew([], [
            'mode'     => config('nhif.mode', 'test'),
            'username' => config('nhif.credentials.username'),
            'password' => config('nhif.credentials.password'),
        ]);
    }
}
