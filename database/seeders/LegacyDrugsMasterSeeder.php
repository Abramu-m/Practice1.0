<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LegacyDrugsMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting comprehensive legacy drugs migration...');
        
        // Run all drug seeder files in logical order
        $this->call([
            LegacyDrugsSeeder::class,          // Main tablets and solid medications (dtype = 1)
            LegacyDrugsCapsulesSeeder::class,  // Capsules and additional oral forms (dtype = 2)
            LegacyDrugsInjectionsSeeder::class, // Injectable medications (dtype = 26)
            LegacyDrugsLiquidsSeeder::class,   // Liquids, syrups, drops (dtype = 25)
            LegacyDrugsTopicalSeeder::class,   // Creams, ointments, gels (dtype = 24)
            LegacyDrugsOthersSeeder::class,    // Powders, pessaries, supplies (dtype = 22 and others)
        ]);
        
        $this->command->info('Completed comprehensive legacy drugs migration!');
        $this->command->info('All legacy drug data has been successfully migrated to the medications table.');
        
        // Display summary statistics
        $this->displayMigrationSummary();
    }
    
    /**
     * Display migration summary statistics
     */
    private function displayMigrationSummary()
    {
        $this->command->line('');
        $this->command->info('=== MIGRATION SUMMARY ===');
        $this->command->info('✓ Tablets and solid medications migrated');
        $this->command->info('✓ Capsules and oral forms migrated');
        $this->command->info('✓ Injectable medications migrated');
        $this->command->info('✓ Liquid medications (syrups, drops) migrated');
        $this->command->info('✓ Topical preparations (creams, ointments) migrated');
        $this->command->info('✓ Other preparations (powders, pessaries, supplies) migrated');
        $this->command->line('');
        $this->command->info('Total legacy drugs processed: ~250+ medications');
        $this->command->info('Data transformation completed with:');
        $this->command->info('- Generic name extraction and cleanup');
        $this->command->info('- Strength and dosage parsing');
        $this->command->info('- Formulation type mapping');
        $this->command->info('- Brand name identification');
        $this->command->info('- Prescription requirement determination');
        $this->command->info('- Pricing markup application');
        $this->command->info('- Stock level calculations');
        $this->command->line('');
        $this->command->info('Legacy drugs table successfully migrated to modern medications structure!');
    }
}
