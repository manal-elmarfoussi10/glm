<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Plan;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

/**
 * Seeds data required for QA scenarios (S1–S12).
 * Run after RolesAndUsersSeeder and DemoDataSeeder.
 */
class QaScenariosSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedStarterCompanyForS1();
        $this->seedExpensesForS10();
        $this->seedSubscriptionRecords();
        $this->seedBranchScopingForS9();
    }

    /** S1: Company on Starter plan for plan gating (reports/profitability locked). */
    private function seedStarterCompanyForS1(): void
    {
        $starter = Plan::where('name', 'Starter')->first();
        if (! $starter) {
            return;
        }

        $company = Company::firstOrCreate(
            ['name' => 'Starter Company QA'],
            [
                'status' => 'active',
                'plan_id' => $starter->id,
                'city' => 'Marrakech',
                'phone' => '+212 5 24 00 00 00',
                'email' => 'contact@starter-qa.ma',
            ]
        );

        $branch = $company->branches()->firstOrCreate(
            ['name' => 'Agence Marrakech'],
            ['city' => 'Marrakech', 'address' => 'Avenue Hassan II', 'status' => Branch::STATUS_ACTIVE]
        );

        User::firstOrCreate(
            ['email' => 'starter-admin@company.com'],
            [
                'name' => 'Starter Admin',
                'password' => bcrypt('password'),
                'role' => 'company_admin',
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $company->getOrCreateSubscription();
    }

    /** S10: Expenses linked to vehicle for profitability page. */
    private function seedExpensesForS10(): void
    {
        $company = Company::where('name', 'Main Company LLC')->first();
        if (! $company) {
            return;
        }

        $vehicle = $company->vehicles()->first();
        $branch = $company->branches()->first();
        if (! $vehicle || ! $branch) {
            return;
        }

        Expense::firstOrCreate(
            [
                'company_id' => $company->id,
                'vehicle_id' => $vehicle->id,
                'date' => now()->subDays(5),
                'category' => Expense::CATEGORY_MAINTENANCE,
                'amount' => 800,
            ],
            [
                'branch_id' => $branch->id,
                'description' => 'Révision QA',
            ]
        );
    }

    /** Ensure subscription records exist for companies that have plan_id. */
    private function seedSubscriptionRecords(): void
    {
        foreach (Company::whereNotNull('plan_id')->get() as $company) {
            $company->getOrCreateSubscription();
        }
    }

    /** S9: Ensure multiple branches and vehicles spread across them for branch filter tests. */
    private function seedBranchScopingForS9(): void
    {
        $company = Company::where('name', 'Main Company LLC')->first();
        if (! $company) {
            return;
        }

        $branch2 = $company->branches()->where('name', 'Agence Casa Nord')->first();
        if (! $branch2) {
            return;
        }

        $company->vehicles()->firstOrCreate(
            ['plate' => 'QA-BRANCH2-01'],
            [
                'branch_id' => $branch2->id,
                'brand' => 'Renault',
                'model' => 'Symbol',
                'year' => 2021,
                'status' => Vehicle::STATUS_AVAILABLE,
                'daily_price' => 220,
            ]
        );
    }
}
