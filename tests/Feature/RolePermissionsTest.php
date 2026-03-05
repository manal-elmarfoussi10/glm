<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_cannot_cancel_reservation(): void
    {
        $company = Company::create(['name' => 'Test Co', 'status' => 'active']);
        $branch = Branch::create(['company_id' => $company->id, 'name' => 'B1', 'city' => 'Casa', 'status' => 'active']);
        $vehicle = Vehicle::create(['branch_id' => $branch->id, 'plate' => 'P1', 'brand' => 'X', 'model' => 'Y', 'status' => 'available']);
        $customer = $company->customers()->create(['name' => 'Client', 'cin' => 'CIN123']);
        $reservation = $company->reservations()->create([
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'pickup_branch_id' => $branch->id,
            'return_branch_id' => $branch->id,
            'reference' => 'RES-001',
            'status' => Reservation::STATUS_CONFIRMED,
            'payment_status' => 'unpaid',
            'start_at' => now()->addDays(1),
            'end_at' => now()->addDays(3),
            'total_price' => 300,
            'confirmed_at' => now(),
        ]);

        $agent = User::factory()->create([
            'role' => 'agent',
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);

        $this->actingAs($agent)
            ->post(route('app.companies.reservations.cancel', [$company, $reservation]))
            ->assertForbidden();
    }

    public function test_company_admin_can_access_reports_route(): void
    {
        $company = Company::create(['name' => 'Test Co', 'status' => 'active']);
        $branch = Branch::create(['company_id' => $company->id, 'name' => 'B1', 'city' => 'Casa', 'status' => 'active']);
        $admin = User::factory()->create([
            'role' => 'company_admin',
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('app.companies.reports.index', $company))
            ->assertOk();
    }

    public function test_agent_cannot_access_reports_route(): void
    {
        $company = Company::create(['name' => 'Test Co', 'status' => 'active']);
        $branch = Branch::create(['company_id' => $company->id, 'name' => 'B1', 'city' => 'Casa', 'status' => 'active']);
        $agent = User::factory()->create([
            'role' => 'agent',
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);

        $this->actingAs($agent)
            ->get(route('app.companies.reports.index', $company))
            ->assertForbidden();
    }
}
