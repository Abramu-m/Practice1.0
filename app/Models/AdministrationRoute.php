<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdministrationRoute extends Model
{
    use HasFactory;

    protected $table = 'administration_routes';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'route_name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer'
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    // Attributes
    public function getDisplayNameAttribute()
    {
        $display = $this->route_name;
        
        return $display;
    }
    
    public function getFullDescriptionAttribute()
    {
        $description = $this->description;
        
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
}
