<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\User;
use App\Models\Patient;
use App\Models\PatientVisit;
use App\Models\Consultation;
use App\Models\IcdDiagnosis;
use App\Models\Doctor;
use App\Models\VisitType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportsTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    public function setUp(): void
    {
        parent::setUp();

        // Facility must be configured for CheckFacilitySetup middleware to allow access
        Facility::create([
            'name' => 'Test Facility',
            'region' => 'Geita',
            'district' => 'Geita',
        ]);

        // Create admin user
        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'is_admin' => true,
        ]);
    }

    /**
     * Test reports dashboard is accessible to admin
     */
    public function test_admin_can_access_reports_dashboard()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reports.index');
    }

    /**
     * Test reports dashboard is not accessible to non-admin
     */
    public function test_non_admin_cannot_access_reports_dashboard()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)
            ->get(route('admin.reports.index'));

        $response->assertStatus(403);
    }

    /**
     * Test malaria monthly (vipimo) report is accessible
     */
    public function test_admin_can_access_malaria_monthly_report()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.malaria-vipimo', [
                'year' => 2026,
                'month' => 6,
            ]));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reports.malaria-vipimo');
    }

    /**
     * Test IDSR weekly report is accessible
     */
    public function test_admin_can_access_idsr_weekly_report()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.idsr-weekly', [
                'year' => 2026,
                'week' => 23,
            ]));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reports.idsr-weekly');
    }

    /**
     * Test STI monthly report is accessible
     */
    public function test_admin_can_access_sti_monthly_report()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.std-sti-monthly', [
                'year' => 2026,
                'month' => 6,
            ]));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reports.std-sti-monthly');
    }

    /**
     * Test medicines monthly report is accessible
     */
    public function test_admin_can_access_medicines_monthly_report()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.medicines-monthly', [
                'year' => 2026,
                'month' => 6,
            ]));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reports.medicines-monthly');
    }

    /**
     * Test tracer medicines report is accessible
     */
    public function test_admin_can_access_tracer_medicines_report()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.tracer-medicines', [
                'year' => 2026,
                'month' => 6,
            ]));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reports.tracer-medicines');
    }

    /**
     * Test low stock medicines report is accessible
     */
    public function test_admin_can_access_low_stock_report()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.low-stock-medicines'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reports.low-stock-medicines');
    }

    /**
     * Test reports return required data
     */
    public function test_malaria_report_contains_required_data()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.malaria-vipimo', [
                'year' => 2026,
                'month' => 6,
            ]));

        // Check that view has required variables
        $response->assertViewHas('facility');
        $response->assertViewHas('date_range');
        $response->assertViewHas('counts');
        $response->assertViewHas('row_totals');
        $response->assertViewHas('month');
        $response->assertViewHas('year');
    }

    /**
     * Test PDF export is accessible
     */
    public function test_admin_can_export_report_as_pdf()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.malaria-vipimo', [
                'year' => 2026,
                'month' => 6,
                'pdf' => 1,
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test report with invalid date parameters
     */
    public function test_report_handles_invalid_dates_gracefully()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.malaria-vipimo', [
                'year' => 2026,
                'month' => 13, // Invalid month
            ]));

        // Should still return 200, just with no data
        $response->assertStatus(200);
    }
}
