<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        Facility::firstOrCreate(
            ['id' => 1],
            [
                'name'               => config('app.clinic_name', 'Janet Healthcare'),
                'slogan'             => config('app.clinic_slogan', 'Your Health, Our Priority'),
                'country'            => config('app.clinic_country', 'Tanzania'),
                'region'             => config('app.clinic_region', 'Geita'),
                'district'           => config('app.clinic_district', 'Geita'),
                'locale'             => config('app.clinic_locale', 'Nkome'),
                'postal'             => config('app.clinic_postal', 'P.O Box 622'),
                'address'            => config('app.clinic_address', 'P.O Box 622, Nkome, Geita'),
                'phone'              => config('app.clinic_phone', '+255 756 123 456'),
                'email'              => config('app.clinic_email', 'info@janethealthcare.com'),
                'nhif_facility_code' => config('nhif.facility_code', ''),
            ]
        );
    }
}
