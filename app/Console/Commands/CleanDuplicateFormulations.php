<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MedicationFormulation;
use Illuminate\Support\Facades\DB;

class CleanDuplicateFormulations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'formulations:clean-duplicates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove duplicate medication formulations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning duplicate medication formulations...');
        
        // Get all formulations grouped by description
        $duplicates = MedicationFormulation::select('description', DB::raw('COUNT(*) as count'))
                                         ->groupBy('description')
                                         ->having('count', '>', 1)
                                         ->get();
        
        if ($duplicates->isEmpty()) {
            $this->info('No duplicates found.');
            return;
        }
        
        $totalRemoved = 0;
        
        foreach ($duplicates as $duplicate) {
            $this->info("Processing duplicates for: {$duplicate->description} ({$duplicate->count} entries)");
            
            // Get all records for this description
            $records = MedicationFormulation::where('description', $duplicate->description)
                                           ->orderBy('id')
                                           ->get();
            
            // Keep the first one, delete the rest
            $first = $records->first();
            $toDelete = $records->skip(1);
            
            foreach ($toDelete as $record) {
                $record->delete();
                $totalRemoved++;
            }
            
            $this->line("  Kept ID {$first->id}, removed " . $toDelete->count() . " duplicates");
        }
        
        $this->info("Cleanup complete! Removed {$totalRemoved} duplicate formulations.");
        $this->info("Current total: " . MedicationFormulation::count() . " formulations");
        
        return 0;
    }
}
