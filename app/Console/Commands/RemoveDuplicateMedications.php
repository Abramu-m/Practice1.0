<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Medication;

class RemoveDuplicateMedications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medications:remove-duplicates {--dry-run : Show what would be removed without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove duplicate medications from the medications table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Scanning for duplicate medications...');
        
        $isDryRun = $this->option('dry-run');
        
        // Find duplicates based on generic_name and strength
        $duplicates = DB::select("
            SELECT generic_name, strength, COUNT(*) as count, GROUP_CONCAT(id ORDER BY id) as ids
            FROM medications 
            WHERE generic_name IS NOT NULL
            GROUP BY generic_name, strength 
            HAVING COUNT(*) > 1
            ORDER BY count DESC
        ");

        if (empty($duplicates)) {
            $this->info('No duplicates found based on generic_name and strength!');
            
            // Also check for description-based duplicates
            $this->info('Checking for description-based duplicates...');
            
            $descriptionDuplicates = DB::select("
                SELECT description, COUNT(*) as count, GROUP_CONCAT(id ORDER BY id) as ids
                FROM medications 
                WHERE description IS NOT NULL
                GROUP BY description 
                HAVING COUNT(*) > 1
                ORDER BY count DESC
            ");
            
            if (empty($descriptionDuplicates)) {
                $this->info('No description-based duplicates found either!');
                return;
            }
            
            $duplicates = $descriptionDuplicates;
            $this->info(count($duplicates) . ' groups of description-based duplicates found.');
        } else {
            $this->info(count($duplicates) . ' groups of duplicates found based on generic_name and strength.');
        }

        $totalToRemove = 0;
        $totalGroups = count($duplicates);

        $this->info("\nDuplicate Groups Found:");
        $this->info(str_repeat('=', 80));

        foreach ($duplicates as $duplicate) {
            $ids = explode(',', $duplicate->ids);
            $keepId = $ids[0]; // Keep the first (oldest) record
            $removeIds = array_slice($ids, 1); // Remove the rest
            
            $totalToRemove += count($removeIds);
            
            if (isset($duplicate->generic_name)) {
                $identifier = "'{$duplicate->generic_name}' ({$duplicate->strength})";
            } else {
                $identifier = "'{$duplicate->description}'";
            }
            
            $this->line("Group: {$identifier}");
            $this->line("  Count: {$duplicate->count} records");
            $this->line("  Keep ID: {$keepId}");
            $this->line("  Remove IDs: " . implode(', ', $removeIds));
            $this->line("");
        }

        $this->info("Summary:");
        $this->info("- Total duplicate groups: {$totalGroups}");
        $this->info("- Total records to remove: {$totalToRemove}");
        
        if ($isDryRun) {
            $this->warn("DRY RUN MODE - No records will be deleted");
            $this->info("Run without --dry-run to actually remove duplicates");
            return;
        }

        if (!$this->confirm("Do you want to proceed with removing {$totalToRemove} duplicate records?")) {
            $this->info("Operation cancelled.");
            return;
        }

        $this->info("Removing duplicates...");
        $removedCount = 0;

        DB::transaction(function () use ($duplicates, &$removedCount) {
            foreach ($duplicates as $duplicate) {
                $ids = explode(',', $duplicate->ids);
                $keepId = $ids[0]; // Keep the first record
                $removeIds = array_slice($ids, 1); // Remove the rest

                if (!empty($removeIds)) {
                    // Before removing, let's merge stock quantities to the kept record
                    $totalStock = DB::table('medications')
                        ->whereIn('id', $ids)
                        ->sum('stock_quantity');
                    
                    // Update the kept record with the total stock
                    DB::table('medications')
                        ->where('id', $keepId)
                        ->update(['stock_quantity' => $totalStock]);

                    // Remove the duplicate records
                    $deleted = DB::table('medications')
                        ->whereIn('id', $removeIds)
                        ->delete();
                    
                    $removedCount += $deleted;
                }
            }
        });

        $this->info("Successfully removed {$removedCount} duplicate medications!");
        $this->info("Stock quantities have been merged to the remaining records.");
        
        // Show final count
        $finalCount = Medication::count();
        $this->info("Final medication count: {$finalCount}");
    }
}
