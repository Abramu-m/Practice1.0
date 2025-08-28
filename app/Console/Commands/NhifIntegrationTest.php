<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NhifService;
use App\Models\NhifMember;
use App\Models\Patient;
use Illuminate\Support\Facades\Log;

class NhifIntegrationTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nhif:test {--card=} {--verify-only} {--sync-tariffs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test NHIF integration and API connectivity';

    /**
     * The NHIF service instance
     *
     * @var NhifService
     */
    protected $nhifService;

    /**
     * Create a new command instance.
     */
    public function __construct(NhifService $nhifService)
    {
        parent::__construct();
        $this->nhifService = $nhifService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🏥 Starting NHIF Integration Test...');
        $this->line('');

        // Test configuration
        $this->testConfiguration();

        // Test member verification if card number provided
        if ($cardNumber = $this->option('card')) {
            $this->testMemberVerification($cardNumber);
        }

        // Test tariff synchronization
        if ($this->option('sync-tariffs')) {
            $this->testTariffSync();
        }

        // If only verification test requested, skip other tests
        if ($this->option('verify-only') && $this->option('card')) {
            return 0;
        }

        // Test database connectivity
        $this->testDatabase();

        // Test sample data creation
        $this->testSampleData();

        $this->info('');
        $this->info('✅ NHIF Integration Test completed successfully!');
        return 0;
    }

    /**
     * Test NHIF configuration
     */
    protected function testConfiguration()
    {
        $this->info('🔧 Testing Configuration...');

        $config = config('nhif');
        
        if (empty($config)) {
            $this->error('❌ NHIF configuration not found!');
            return;
        }

        $requiredKeys = ['username', 'password', 'facility_code', 'api_url'];
        $missing = [];

        foreach ($requiredKeys as $key) {
            if (empty($config[$key])) {
                $missing[] = $key;
            }
        }

        if (!empty($missing)) {
            $this->error('❌ Missing configuration keys: ' . implode(', ', $missing));
            $this->warn('💡 Please update your config/nhif.php and .env files');
        } else {
            $this->info('✅ Configuration looks good');
        }

        $this->line('');
    }

    /**
     * Test member verification
     */
    protected function testMemberVerification(string $cardNumber)
    {
        $this->info("🔍 Testing Member Verification for Card: {$cardNumber}");

        try {
            $result = $this->nhifService->verifyMember($cardNumber);

            if ($result['success']) {
                $this->info('✅ Member verification successful');
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['Card Number', $result['data']['CardNo'] ?? 'N/A'],
                        ['Full Name', $result['data']['FullName'] ?? 'N/A'],
                        ['Scheme', $result['data']['SchemeName'] ?? 'N/A'],
                        ['Status', $result['data']['MembershipStatus'] ?? 'N/A'],
                        ['Employer', $result['data']['EmployerName'] ?? 'N/A'],
                    ]
                );
            } else {
                $this->error('❌ Member verification failed');
                $this->warn('Error: ' . $result['message']);
            }

        } catch (\Exception $e) {
            $this->error('❌ Exception during member verification: ' . $e->getMessage());
        }

        $this->line('');
    }

    /**
     * Test tariff synchronization
     */
    protected function testTariffSync()
    {
        $this->info('💰 Testing Tariff Synchronization...');

        try {
            $result = $this->nhifService->syncTariffs();

            if ($result['success']) {
                $this->info('✅ Tariff sync successful');
                $this->info("Synced {$result['count']} tariffs");
            } else {
                $this->error('❌ Tariff sync failed: ' . $result['message']);
            }

        } catch (\Exception $e) {
            $this->error('❌ Exception during tariff sync: ' . $e->getMessage());
        }

        $this->line('');
    }

    /**
     * Test database connectivity
     */
    protected function testDatabase()
    {
        $this->info('🗄️  Testing Database Connectivity...');

        try {
            // Test NHIF models
            $membersCount = NhifMember::count();
            $this->info("✅ Found {$membersCount} NHIF members in database");

            $patientsCount = Patient::count();
            $this->info("✅ Found {$patientsCount} patients in database");

        } catch (\Exception $e) {
            $this->error('❌ Database connection failed: ' . $e->getMessage());
        }

        $this->line('');
    }

    /**
     * Test sample data creation
     */
    protected function testSampleData()
    {
        $this->info('📊 Testing Sample Data Creation...');

        if (!$this->confirm('Create sample NHIF member record?')) {
            $this->warn('⚠️  Skipping sample data creation');
            return;
        }

        try {
            // Create a sample patient if none exists
            $patient = Patient::first();
            
            if (!$patient) {
                $this->warn('⚠️  No patients found in database. Please add patients first.');
                return;
            }

            // Create sample NHIF member
            $member = NhifMember::updateOrCreate(
                ['patient_id' => $patient->id],
                [
                    'card_no' => 'TEST123456789',
                    'full_name' => $patient->full_name ?? 'Test Patient',
                    'scheme_id' => 'NHIF001',
                    'scheme_name' => 'Test Scheme',
                    'membership_status' => 'Active',
                    'authorization_no' => 'AUTH' . rand(100000, 999999),
                    'employer_name' => 'Test Employer',
                    'verification_status' => 'verified',
                    'verified_at' => now(),
                ]
            );

            $this->info('✅ Sample NHIF member created/updated');
            $this->table(
                ['Field', 'Value'],
                [
                    ['Patient ID', $member->patient_id],
                    ['Card Number', $member->card_no],
                    ['Full Name', $member->full_name],
                    ['Status', $member->membership_status],
                ]
            );

        } catch (\Exception $e) {
            $this->error('❌ Failed to create sample data: ' . $e->getMessage());
        }

        $this->line('');
    }
}
