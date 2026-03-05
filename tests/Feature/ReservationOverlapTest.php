<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationOverlapTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_confirmed_reservation_overlapping_existing_returns_validation_error(): void
    {
        $company = Company::create(['name' => 'Co', 'status' => 'active']);
        $branch = Branch::create(['company_id' => $company->id, 'name' => 'B1', 'city' => 'Casa', 'status' => 'active']);
        $vehicle = Vehicle::create(['branch_id' => $branch->id, 'plate' => 'P1', 'brand' => 'X', 'model' => 'Y', 'status' => 'available']);
        $customer = $company->customers()->create(['name' => 'Client', 'cin' => 'CIN1']);

        $start = Carbon::tomorrow()->setTime(9, 0);
        $end = $start->copy()->addDays(2)->setTime(18, 0);
        $company->reservations()->create([
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'pickup_branch_id' => $branch->id,
            'return_branch_id' => $branch->id,
            'reference' => 'RES-001',
            'status' => Reservation::STATUS_CONFIRMED,
            'payment_status' => 'unpaid',
            'start_at' => $start,
            'end_at' => $end,
            'total_price' => 500,
            'confirmed_at' => now(),
        ]);

        $admin = User::factory()->create([
            'role' => 'company_admin',
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);

        $overlapStart = $start->copy()->addDay()->format('Y-m-d\T09:00');
        $overlapEnd = $end->copy()->subDay()->format('Y-m-d\T18:00');

        $response = $this->actingAs($admin)->post(route('app.companies.reservations.store', $company), [
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'pickup_branch_id' => $branch->id,
            'return_branch_id' => $branch->id,
            'start_at' => $overlapStart,
            'end_at' => $overlapEnd,
            'total_price' => 300,
            'status' => Reservation::STATUS_CONFIRMED,
        ]);

        $response->assertSessionHasErrors('start_at');
        $this->assertStringContainsString('déjà réservé', $response->getSession()->get('errors')->first('start_at'));
    }

    public function test_confirming_draft_reservation_that_would_overlap_returns_error(): void
    {
        $company = Company::create(['name' => 'Co', 'status' => 'active']);
        $branch = Branch::create(['company_id' => $company->id, 'name' => 'B1', 'city' => 'Casa', 'status' => 'active']);
        $vehicle = Vehicle::create(['branch_id' => $branch->id, 'plate' => 'P1', 'brand' => 'X', 'model' => 'Y', 'status' => 'available']);
        $customer = $company->customers()->create(['name' => 'Client', 'cin' => 'CIN1']);

        $start = Carbon::tomorrow()->setTime(9, 0);
        $end = $start->copy()->addDays(2);
        $company->reservations()->create([
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'pickup_branch_id' => $branch->id,
            'return_branch_id' => $branch->id,
            'reference' => 'RES-CONF',
            'status' => Reservation::STATUS_CONFIRMED,
            'payment_status' => 'unpaid',
            'start_at' => $start,
            'end_at' => $end,
            'total_price' => 500,
            'confirmed_at' => now(),
        ]);

        $draft = $company->reservations()->create([
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'pickup_branch_id' => $branch->id,
            'return_branch_id' => $branch->id,
            'reference' => 'RES-DRAFT',
            'status' => Reservation::STATUS_DRAFT,
            'payment_status' => 'unpaid',
            'start_at' => $start->copy()->addDay(),
            'end_at' => $end,
            'total_price' => 300,
        ]);

        $admin = User::factory()->create([
            'role' => 'company_admin',
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('app.companies.reservations.confirm', [$company, $draft]));

        $response->assertSessionHas('error');
        $this->assertStringContainsString('déjà réservé', $response->getSession()->get('error'));
    }
}
