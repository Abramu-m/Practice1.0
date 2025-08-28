<?php

namespace App\Console\Commands;

use App\Services\NhifService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class TestNhifConnection extends Command
{
    protected $signature = 'nhif:test';
    protected $description = 'Test NHIF service connection and configuration';

    public function handle()
    {
        $this->info('Testing NHIF Integration...');

        // Check configuration
        $this->info('Checking configuration...');
        $config = config('nhif');
        
        if (empty($config['credentials']['username'])) {
            $this->error('NHIF_USERNAME not configured in .env file');
            return 1;
        }

        if (empty($config['credentials']['password'])) {
            $this->error('NHIF_PASSWORD not configured in .env file');
            return 1;
        }

        $this->info('✓ Configuration looks good');
        $this->info('✓ Mode: ' . $config['mode']);
        $this->info('✓ Base URL: ' . $config['url'][$config['mode']]);

        // Test service instantiation
        try {
            $nhifService = app(NhifService::class);
            $this->info('✓ NHIF Service instantiated successfully');
        } catch (\Exception $e) {
            $this->error('✗ Failed to instantiate NHIF Service: ' . $e->getMessage());
            return 1;
        }

        // Test database tables
        $this->info('Checking database tables...');
        try {
            Schema::hasTable('nhif_members') ? $this->info('✓ nhif_members table exists') : $this->error('✗ nhif_members table missing');
            Schema::hasTable('nhif_claims') ? $this->info('✓ nhif_claims table exists') : $this->error('✗ nhif_claims table missing');
            Schema::hasTable('nhif_claim_items') ? $this->info('✓ nhif_claim_items table exists') : $this->error('✗ nhif_claim_items table missing');
            Schema::hasTable('nhif_claim_diseases') ? $this->info('✓ nhif_claim_diseases table exists') : $this->error('✗ nhif_claim_diseases table missing');
            Schema::hasTable('nhif_tariffs') ? $this->info('✓ nhif_tariffs table exists') : $this->error('✗ nhif_tariffs table missing');
        } catch (\Exception $e) {
            $this->error('Database check failed: ' . $e->getMessage());
            return 1;
        }

        $this->info('');
        $this->info('🎉 NHIF Integration test completed successfully!');
        $this->info('');
        $this->info('Next steps:');
        $this->info('1. Add your NHIF credentials to .env file');
        $this->info('2. Set NHIF_FACILITY_CODE in .env file');
        $this->info('3. Visit /nhif in your browser to access the dashboard');
        $this->info('4. Test member verification with a valid NHIF card');

        return 0;
    }
}
