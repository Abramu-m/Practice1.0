<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicineDispensingReportRow extends Model
{
    protected $fillable = [
        'row_key',
        'group_key',
        'row_no',
        'row_no_rowspan',
        'drug_label',
        'drug_rowspan',
        'unit_label',
        'medication_id',
        'sort_order',
    ];

    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }
}
