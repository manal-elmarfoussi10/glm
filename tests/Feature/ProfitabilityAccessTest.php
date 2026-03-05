<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfitabilityAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_starter_company_sees_locked_profitability_page(): void
    {
        $plan = Plan::create([
            'name' => 'Starter',
            'monthly_price' => 99,
            'is_active' => true,
            'features_limits' => ['features' => ['profitability' => false]],
        ]);
        $company = Company::create(['name' => 'Starter Co', 'status' => 'active', 'plan_id' => $plan->id]);
        $branch = Branch::create(['company_id' => $company->id, 'name' => 'B1', 'city' => 'Casa', 'status' => 'active']);
        $user = User::factory()->create([
            'role' => 'company_admin',
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)
            ->get(route('app.companies.fleet.profitability.index', $company));
        $response->assertOk();
        $response->assertSee('réservée', false);
        $response->assertSee('mettre à niveau', false);
    }

    public function test_pro_company_can_access_profitability_index(): void
    {
        $plan = Plan::create([
            'name' => 'Pro',
            'monthly_price' => 299,
            'is_active' => true,
            'features_limits' => ['features' => ['profitability' => true]],
        ]);
        $company = Company::create(['name' => 'Pro Co', 'status' => 'active', 'plan_id' => $plan->id]);
        $branch = Branch::create(['company_id' => $company->id, 'name' => 'B1', 'city' => 'Casa', 'status' => 'active']);
        $user = User::factory()->create([
            'role' => 'company_admin',
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)
            ->get(route('app.companies.fleet.profitability.index', $company));
        $response->assertOk();
        $response->assertSee('Revenus flotte', false);
        $response->assertSee('Coûts flotte', false);
    }
}
