<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestDesignation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-designation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Doctor-Designation relationships...');
        
        // Check designations
        $designations = \App\Models\Designation::all();
        $this->info('Total Designations: ' . $designations->count());
        foreach($designations as $designation) {
            $this->line('Code: ' . $designation->designation_code . ', Description: ' . $designation->description);
        }
        
        // Check doctors
        $doctors = \App\Models\Doctor::all();
        $this->info('Total Doctors: ' . $doctors->count());
        foreach($doctors as $doctor) {
            $this->line('Doctor ID: ' . $doctor->doctor_id . ', Designation Value: ' . ($doctor->designation ?? 'NULL'));
            
            // Try to load designation relationship
            $designationInfo = $doctor->designationInfo;
            if($designationInfo) {
                $this->line('  -> Designation Description: ' . $designationInfo->description);
            } else {
                $this->error('  -> No designation found for doctor ' . $doctor->doctor_id);
            }
        }
    }
}
