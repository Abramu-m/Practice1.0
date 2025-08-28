<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdministrationRoute extends Model
{
    use HasFactory;

    protected $table = 'administration_routes'; // Better than 'route'
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'route_name',
        'route_code',
        'route_abbreviation',
        'description',
        'instructions',
        'requires_prescription',
        'is_active',
        'display_order'
    ];

    protected $casts = [
        'requires_prescription' => 'boolean',
        'is_active' => 'boolean',
        'display_order' => 'integer'
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeByDisplayOrder($query)
    {
        return $query->orderBy('display_order');
    }
    
    public function scopeRequiringPrescription($query)
    {
        return $query->where('requires_prescription', true);
    }
    
    public function scopeNotRequiringPrescription($query)
    {
        return $query->where('requires_prescription', false);
    }
    
    // Attributes
    public function getDisplayNameAttribute()
    {
        $display = $this->route_name;
        
        if ($this->route_abbreviation) {
            $display .= " ({$this->route_abbreviation})";
        }
        
        return $display;
    }
    
    public function getFullDescriptionAttribute()
    {
        $description = $this->description;
        
        if ($this->instructions) {
            $description .= "\n\nInstructions: " . $this->instructions;
        }
        
        return $description;
    }
    
    // Relationships
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }
    
    public function medications()
    {
        return $this->belongsToMany(Medication::class, 'medication_routes')
                    ->withTimestamps();
    }
    
    // Helper methods
    public function isActive()
    {
        return $this->is_active;
    }
    
    public function requiresPrescription()
    {
        return $this->requires_prescription;
    }
    
    public function canBeUsedForOTC()
    {
        return !$this->requires_prescription;
    }
}
