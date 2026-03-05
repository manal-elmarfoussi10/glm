<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_admin_cannot_access_another_company(): void
    {
        $companyA = Company::create(['name' => 'Company A', 'status' => 'active']);
        $companyB = Company::create(['name' => 'Company B', 'status' => 'active']);
        Branch::create(['company_id' => $companyB->id, 'name' => 'Branch B', 'city' => 'Rabat', 'status' => 'active']);

        $adminA = User::factory()->create([
            'role' => 'company_admin',
            'company_id' => $companyA->id,
            'branch_id' => null,
        ]);

        $this->actingAs($adminA);

        $this->get(route('app.companies.show', $companyB))->assertForbidden();
        $this->get(route('app.companies.vehicles.index', $companyB))->assertForbidden();
        $this->get(route('app.companies.reservations.index', $companyB))->assertForbidden();
        $this->get(route('app.companies.customers.index', $companyB))->assertForbidden();
    }

    public function test_agent_cannot_access_another_company(): void
    {
        $companyA = Company::create(['name' => 'Company A', 'status' => 'active']);
        $companyB = Company::create(['name' => 'Company B', 'status' => 'active']);

        $agentA = User::factory()->create([
            'role' => 'agent',
            'company_id' => $companyA->id,
        ]);

        $this->actingAs($agentA);
        $this->get(route('app.companies.show', $companyB))->assertForbidden();
    }

    public function test_company_admin_cannot_see_other_company_vehicles_via_direct_id(): void
    {
        $companyA = Company::create(['name' => 'Company A', 'status' => 'active']);
        $companyB = Company::create(['name' => 'Company B', 'status' => 'active']);
        $branchA = Branch::create(['company_id' => $companyA->id, 'name' => 'A1', 'city' => 'Casa', 'status' => 'active']);
        $branchB = Branch::create(['company_id' => $companyB->id, 'name' => 'B1', 'city' => 'Rabat', 'status' => 'active']);
        $vehicleB = Vehicle::create(['branch_id' => $branchB->id, 'plate' => 'XX-00000', 'brand' => 'X', 'model' => 'Y', 'status' => 'available']);

        $adminA = User::factory()->create(['role' => 'company_admin', 'company_id' => $companyA->id]);
        $this->actingAs($adminA);

        $response = $this->get(route('app.companies.vehicles.show', [$companyA, $vehicleB]));
        $response->assertNotFound();
    }
}
