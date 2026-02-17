<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Allergy;
use App\Models\Medication;

class FixAllergyNames extends Command
{
    protected $signature = 'fix:allergy-names';
    protected $description = 'Fix allergy substance names that are medication IDs instead of names';

    public function handle()
    {
        $this->info('Fixing allergy substance names...');
        
        $allergies = Allergy::all();
        $fixed = 0;
        $skipped = 0;
        
        foreach ($allergies as $allergy) {
            // Check if substance_name looks like an ID (numeric or short string with letters/numbers)
            $substanceName = $allergy->substance_name;
            $looksSuspicious = is_numeric($substanceName) || 
                               strlen($substanceName) < 4 || 
                               preg_match('/^[a-z0-9]{2,6}$/i', $substanceName);
            
            if ($looksSuspicious) {
                // Try to find medication by ID first
                $medication = Medication::find($substanceName);
                
                // If not found by ID, try by code
                if (!$medication) {
                    $medication = Medication::where('code', $substanceName)->first();
                }
                
                if ($medication) {
                    $oldName = $allergy->substance_name;
                    $newName = $medication->generic_name;
                    
                    $allergy->substance_name = $newName;
                    $allergy->save();
                    
                    $this->line("✓ Fixed: '{$oldName}' → '{$newName}'");
                    $fixed++;
                } else {
                    $this->warn("✗ Could not find medication for: {$allergy->substance_name} (ID#{$allergy->id})");
                    $skipped++;
                }
            } else {
                $skipped++;
            }
        }
        
        $this->info("\nDone! Fixed: {$fixed}, Skipped: {$skipped}");
        
        return 0;
    }
}
