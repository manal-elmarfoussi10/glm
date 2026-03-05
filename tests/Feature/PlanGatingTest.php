<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Plan;
use App\Models\User;
use App\Services\PlanGateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanGatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_starter_plan_has_no_profitability_feature(): void
    {
        $plan = Plan::create([
            'name' => 'Starter',
            'monthly_price' => 99,
            'is_active' => true,
            'features_limits' => [
                'features' => [
                    'profitability' => false,
                    'reports' => false,
                ],
            ],
        ]);
        $this->assertFalse($plan->hasFeature(PlanGateService::FEATURE_PROFITABILITY));
    }

    public function test_pro_plan_has_profitability_feature(): void
    {
        $plan = Plan::create([
            'name' => 'Pro',
            'monthly_price' => 299,
            'is_active' => true,
            'features_limits' => [
                'features' => [
                    'profitability' => true,
                    'reports' => true,
                ],
            ],
        ]);
        $this->assertTrue($plan->hasFeature(PlanGateService::FEATURE_PROFITABILITY));
    }

    public function test_plan_gate_service_denies_feature_when_plan_has_it_disabled(): void
    {
        $plan = Plan::create([
            'name' => 'Starter',
            'monthly_price' => 99,
            'is_active' => true,
            'features_limits' => ['features' => ['profitability' => false]],
        ]);
        $company = Company::create(['name' => 'Co', 'status' => 'active', 'plan_id' => $plan->id]);
        $gate = app(PlanGateService::class);
        $this->assertFalse($gate->can($company, PlanGateService::FEATURE_PROFITABILITY));
    }

    public function test_vehicle_limit_reached_returns_true_when_at_limit(): void
    {
        $plan = Plan::create([
            'name' => 'Starter',
            'monthly_price' => 99,
            'limit_vehicles' => 2,
            'is_active' => true,
        ]);
        $company = Company::create(['name' => 'Co', 'status' => 'active', 'plan_id' => $plan->id]);
        $branch = Branch::create(['company_id' => $company->id, 'name' => 'B1', 'city' => 'Casa', 'status' => 'active']);
        $company->vehicles()->create(['branch_id' => $branch->id, 'plate' => 'P1', 'brand' => 'X', 'model' => 'Y', 'status' => 'available']);
        $company->vehicles()->create(['branch_id' => $branch->id, 'plate' => 'P2', 'brand' => 'X', 'model' => 'Z', 'status' => 'available']);

        $gate = app(PlanGateService::class);
        $this->assertTrue($gate->isLimitReached($company, PlanGateService::LIMIT_VEHICLES));
    }
}
