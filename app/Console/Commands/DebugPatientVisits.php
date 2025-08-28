<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DebugPatientVisits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:debug-patient-visits';

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
        $this->info('=== Debugging Patient Visit Relationships ===');
        
        // Check if we have any patient visits
        $visits = \App\Models\PatientVisit::all();
        $this->info('Total visits: ' . $visits->count());
        
        if ($visits->count() > 0) {
            $visit = $visits->first();
            $this->info('First visit ID: ' . $visit->id);
            $this->info('Patient foreign key value: ' . $visit->patient);
            
            // Check if patient exists
            $patient = \App\Models\Patient::find($visit->patient);
            if ($patient) {
                $this->info('Patient found with ID: ' . $patient->id);
                $this->info('Patient first name: ' . $patient->first_name);
                $this->info('Patient middle name: ' . $patient->middle_name);
                $this->info('Patient last name: ' . $patient->last_name);
                $this->info('Patient full name: ' . $patient->full_name);
            } else {
                $this->error('Patient not found with ID: ' . $visit->patient);
            }
            
            // Test the relationship
            $visitWithPatient = \App\Models\PatientVisit::with('patient')->find($visit->id);
            if ($visitWithPatient && $visitWithPatient->patient) {
                $this->info('Relationship working: ' . $visitWithPatient->patient->full_name);
            } else {
                $this->error('Relationship not working - patient is null');
            }
        } else {
            $this->warn('No patient visits found in database');
        }
        
        // Check patients
        $patients = \App\Models\Patient::all();
        $this->info('Total patients: ' . $patients->count());
        
        if ($patients->count() > 0) {
            $patient = $patients->first();
            $this->info('First patient ID: ' . $patient->id);
            $this->info('First patient full name: ' . $patient->full_name);
        }
    }
}
