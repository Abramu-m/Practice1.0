<?php

namespace Tests\Feature;

use App\Models\NhifMember;
use App\Models\User;
use App\Services\NhifService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NhifAuthorizeTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorize_success_updates_member()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $mock = \Mockery::mock(NhifService::class);
        $mock->shouldReceive('authorizeCard')->once()->andReturn([
            'success' => true,
            'data' => [
                'CardNo' => 'CARD123',
                'AuthorizationStatus' => 'Accepted',
                'AuthorizationFacility' => 'Test Facility',
                'AuthorizationDate' => now()->toDateString(),
                'AuthorizationNo' => 'AUTH-001',
                'FirstName' => 'John',
                'LastName' => 'Doe',
            ]
        ]);

        $this->app->instance(NhifService::class, $mock);

        $resp = $this->postJson(route('nhif.authorize'), [
            'card_number' => 'CARD123',
            'visit_type_id' => 1,
            'referral_number' => null,
            'remarks' => 'Testing',
        ]);

        $resp->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseHas('nhif_members', [
            'card_no' => 'CARD123',
            'authorization_no' => 'AUTH-001',
        ]);
    }

    public function test_authorize_blocked_when_already_authorized_elsewhere_today()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create an existing NHIF member authorized today at a different facility
        NhifMember::create([
            'card_no' => 'CARD999',
            'authorization_status' => 'Facility: Other Facility; Date: ' . now()->format('Y-m-d') . '; Status: Accepted;',
            'authorization_no' => 'AUTH-OLD',
        ]);

        // Ensure service is not called when blocked
        $mock = \Mockery::mock(NhifService::class);
        $mock->shouldNotReceive('authorizeCard');
        $this->app->instance(NhifService::class, $mock);

        $resp = $this->postJson(route('nhif.authorize'), [
            'card_number' => 'CARD999',
            'visit_type_id' => 1, // non-emergency
        ]);

        $resp->assertStatus(422)->assertJson(['success' => false]);
    }

    public function test_authorize_allows_override_emergency()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Existing same-day authorization at another facility
        NhifMember::create([
            'card_no' => 'CARD777',
            'authorization_status' => 'Facility: Other Facility; Date: ' . now()->format('Y-m-d') . '; Status: Accepted;',
            'authorization_no' => 'AUTH-OLD-2',
        ]);

        $mock = \Mockery::mock(NhifService::class);
        $mock->shouldReceive('authorizeCard')->once()->andReturn([
            'success' => true,
            'data' => [
                'CardNo' => 'CARD777',
                'AuthorizationStatus' => 'Accepted',
                'AuthorizationFacility' => config('nhif.facility_name') ?? 'Local Facility',
                'AuthorizationDate' => now()->toDateString(),
                'AuthorizationNo' => 'AUTH-NEW-777',
            ]
        ]);

        $this->app->instance(NhifService::class, $mock);

        $resp = $this->postJson(route('nhif.authorize'), [
            'card_number' => 'CARD777',
            'visit_type_id' => 1,
            'override_emergency' => '1',
        ]);

        $resp->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseHas('nhif_members', [
            'card_no' => 'CARD777',
            'authorization_no' => 'AUTH-NEW-777',
        ]);
    }
}
