<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesAndUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin
        \App\Models\User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'super_admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Create Company
        $company = \App\Models\Company::create([
            'name' => 'Main Company LLC',
            'status' => 'active',
        ]);

        // Create Branch
        $branch = \App\Models\Branch::create([
            'company_id' => $company->id,
            'name' => 'Headquarters',
            'status' => 'active',
        ]);

        // Create Company Admin
        \App\Models\User::create([
            'name' => 'Company Admin',
            'email' => 'admin@company.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'company_admin',
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }
}
