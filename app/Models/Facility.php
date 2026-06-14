<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\ClientManager;

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
        'email_domain',
        'imap_host',
        'imap_port',
        'imap_encryption',
        'nhif_facility_code',
        'hfr_code',
        'logo',
        'in_charge',
    ];

    /**
     * The user currently in charge of the facility.
     */
    public function inCharge()
    {
        return $this->belongsTo(User::class, 'in_charge');
    }

    /**
     * Build an (unconnected) IMAP client for a mailbox on this facility's mail server.
     */
    public function makeImapClient(string $username, string $password): Client
    {
        return (new ClientManager(config('imap')))->make([
            'host' => $this->imap_host,
            'port' => $this->imap_port,
            'protocol' => 'imap',
            'encryption' => in_array($this->imap_encryption, ['ssl', 'tls', 'starttls']) ? $this->imap_encryption : false,
            'validate_cert' => true,
            'username' => $username,
            'password' => $password,
            'authentication' => null,
        ]);
    }

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
