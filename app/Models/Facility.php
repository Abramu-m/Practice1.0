<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    protected $fillable = [
        'name',
        'slogan',
        'country',
        'region',
        'district',
        'locale',
        'postal',
        'address',
        'phone',
        'email',
        'nhif_facility_code',
        'hfr_code',
        'logo',
    ];

    /**
     * Always return the single facility record, or a default stub if none exists.
     */
    public static function current(): static
    {
        return static::firstOrNew([], [
            'name'    => config('app.clinic_name', 'Medical Facility'),
            'slogan'  => config('app.clinic_slogan'),
            'country' => config('app.clinic_country'),
            'region'  => config('app.clinic_region'),
            'district'=> config('app.clinic_district'),
            'locale'  => config('app.clinic_locale'),
            'postal'  => config('app.clinic_postal'),
            'address' => config('app.clinic_address'),
            'phone'   => config('app.clinic_phone'),
            'email'   => config('app.clinic_email'),
            'nhif_facility_code' => config('nhif.facility_code'),
        ]);
    }
}
