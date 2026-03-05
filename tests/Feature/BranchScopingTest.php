<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Reservation;
use App\Models\ReservationPayment;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchScopingTest extends TestCase
{
    use RefreshDatabase;

    public function test_vehicles_index_can_filter_by_branch_id(): void
    {
        $company = Company::create(['name' => 'Co', 'status' => 'active']);
        $branchA = Branch::create(['company_id' => $company->id, 'name' => 'A', 'city' => 'Casa', 'status' => 'active']);
        $branchB = Branch::create(['company_id' => $company->id, 'name' => 'B', 'city' => 'Rabat', 'status' => 'active']);
        Vehicle::create(['branch_id' => $branchA->id, 'plate' => 'P-A1', 'brand' => 'X', 'model' => 'Y', 'status' => 'available']);
        Vehicle::create(['branch_id' => $branchB->id, 'plate' => 'P-B1', 'brand' => 'X', 'model' => 'Z', 'status' => 'available']);

        $user = User::factory()->create([
            'role' => 'company_admin',
            'company_id' => $company->id,
            'branch_id' => $branchA->id,
            'status' => 'active',
        ]);

        $this->actingAs($user);

        $resAll = $this->get(route('app.companies.vehicles.index', $company));
        $resAll->assertOk();
        $resAll->assertSee('P-A1');
        $resAll->assertSee('P-B1');

        $resBranchA = $this->get(route('app.companies.vehicles.index', [$company, 'branch_id' => $branchA->id]));
        $resBranchA->assertOk();
        $resBranchA->assertSee('P-A1');
        $resBranchA->assertDontSee('P-B1');

        $resBranchB = $this->get(route('app.companies.vehicles.index', [$company, 'branch_id' => $branchB->id]));
        $resBranchB->assertOk();
        $resBranchB->assertSee('P-B1');
        $resBranchB->assertDontSee('P-A1');
    }

    public function test_reservations_index_can_filter_by_branch_id(): void
    {
        $company = Company::create(['name' => 'Co', 'status' => 'active']);
        $branchA = Branch::create(['company_id' => $company->id, 'name' => 'A', 'city' => 'Casa', 'status' => 'active']);
        $branchB = Branch::create(['company_id' => $company->id, 'name' => 'B', 'city' => 'Rabat', 'status' => 'active']);
        $vehicleA = Vehicle::create(['branch_id' => $branchA->id, 'plate' => 'P-A', 'brand' => 'X', 'model' => 'Y', 'status' => 'available']);
        $vehicleB = Vehicle::create(['branch_id' => $branchB->id, 'plate' => 'P-B', 'brand' => 'X', 'model' => 'Z', 'status' => 'available']);
        $customer = $company->customers()->create(['name' => 'Client', 'cin' => 'C1']);
        $resA = $company->reservations()->create([
            'vehicle_id' => $vehicleA->id,
            'customer_id' => $customer->id,
            'pickup_branch_id' => $branchA->id,
            'return_branch_id' => $branchA->id,
            'reference' => 'RES-A',
            'status' => Reservation::STATUS_CONFIRMED,
            'payment_status' => 'unpaid',
            'start_at' => now()->addDays(1),
            'end_at' => now()->addDays(3),
            'total_price' => 300,
            'confirmed_at' => now(),
        ]);
        $resB = $company->reservations()->create([
            'vehicle_id' => $vehicleB->id,
            'customer_id' => $customer->id,
            'pickup_branch_id' => $branchB->id,
            'return_branch_id' => $branchB->id,
            'reference' => 'RES-B',
            'status' => Reservation::STATUS_CONFIRMED,
            'payment_status' => 'unpaid',
            'start_at' => now()->addDays(5),
            'end_at' => now()->addDays(7),
            'total_price' => 400,
            'confirmed_at' => now(),
        ]);

        $user = User::factory()->create([
            'role' => 'company_admin',
            'company_id' => $company->id,
            'branch_id' => $branchA->id,
            'status' => 'active',
        ]);

        $this->actingAs($user);

        $resFilterA = $this->get(route('app.companies.reservations.index', [$company, 'branch_id' => $branchA->id]));
        $resFilterA->assertOk();
        $resFilterA->assertSee('RES-A');
        $resFilterA->assertDontSee('RES-B');

        $resFilterB = $this->get(route('app.companies.reservations.index', [$company, 'branch_id' => $branchB->id]));
        $resFilterB->assertOk();
        $resFilterB->assertSee('RES-B');
        $resFilterB->assertDontSee('RES-A');
    }
}
