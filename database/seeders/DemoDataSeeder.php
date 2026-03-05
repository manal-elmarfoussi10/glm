<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\ContractTemplate;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Reservation;
use App\Models\ReservationContract;
use App\Models\ReservationInspection;
use App\Models\ReservationInspectionPhoto;
use App\Models\ReservationPayment;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPlans();
        $company1 = Company::where('name', 'Main Company LLC')->first();
        $company2 = $this->seedSecondCompany();
        if ($company1) {
            $this->seedBranchesAndUsers($company1);
            $this->seedVehiclesAndCustomersAndReservations($company1);
        }
        $this->seedSecondCompanyData($company2);
    }

    private function seedPlans(): void
    {
        Plan::firstOrCreate(
            ['name' => 'Starter'],
            [
                'monthly_price' => 99,
                'yearly_price' => 990,
                'trial_days' => 14,
                'limit_vehicles' => 5,
                'limit_users' => 2,
                'limit_branches' => 1,
                'is_active' => true,
                'features_limits' => [
                    'features' => [
                        'reports' => false,
                        'profitability' => false,
                        'partner_availability' => false,
                    ],
                ],
            ]
        );
        Plan::firstOrCreate(
            ['name' => 'Pro'],
            [
                'monthly_price' => 299,
                'yearly_price' => 2990,
                'trial_days' => 14,
                'limit_vehicles' => 50,
                'limit_users' => 10,
                'limit_branches' => 5,
                'is_active' => true,
                'features_limits' => [
                    'features' => [
                        'reports' => true,
                        'profitability' => true,
                        'partner_availability' => true,
                    ],
                ],
            ]
        );
    }

    private function seedSecondCompany(): Company
    {
        $plan = Plan::where('name', 'Pro')->first();
        $company = Company::firstOrCreate(
            ['name' => 'Other Company SARL'],
            [
                'status' => 'active',
                'plan_id' => $plan?->id,
                'city' => 'Rabat',
                'phone' => '+212 5 37 00 00 01',
                'email' => 'contact@othercompany.ma',
            ]
        );
        $branch = $company->branches()->firstOrCreate(
            ['name' => 'Agence Rabat'],
            ['city' => 'Rabat', 'address' => 'Avenue Mohammed V', 'status' => Branch::STATUS_ACTIVE]
        );
        User::firstOrCreate(
            ['email' => 'agent@company.com'],
            [
                'name' => 'Agent User',
                'password' => bcrypt('password'),
                'role' => 'agent',
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        return $company;
    }

    private function seedBranchesAndUsers(Company $company): void
    {
        $company->plan_id = Plan::where('name', 'Pro')->first()?->id;
        $company->save();

        $branch1 = $company->branches()->first();
        if (! $branch1) {
            $branch1 = $company->branches()->create([
                'name' => 'Headquarters',
                'city' => 'Casablanca',
                'address' => '123 Boulevard Zerktouni',
                'status' => Branch::STATUS_ACTIVE,
            ]);
        }
        $branch2 = $company->branches()->firstOrCreate(
            ['name' => 'Agence Casa Nord'],
            ['company_id' => $company->id, 'city' => 'Casablanca', 'address' => '45 Rue Ibnou Batouta', 'status' => Branch::STATUS_ACTIVE]
        );

        $admin = User::where('email', 'admin@company.com')->first();
        if ($admin && ! $admin->company_id) {
            $admin->update(['company_id' => $company->id, 'branch_id' => $branch1->id]);
        }
    }

    private function seedVehiclesAndCustomersAndReservations(Company $company): void
    {
        $branch1 = $company->branches()->first();
        $branch2 = $company->branches()->skip(1)->first() ?? $branch1;

        $vehicles = [];
        foreach (['AB-12345' => ['Renault', 'Clio', 'economy'], 'CD-67890' => ['Peugeot', '208', 'economy'], 'EF-11111' => ['Dacia', 'Sandero', 'sedan']] as $plate => $data) {
            $v = $company->vehicles()->where('plate', $plate)->first();
            if (! $v) {
                $v = Vehicle::create([
                    'branch_id' => $branch1->id,
                    'plate' => $plate,
                    'brand' => $data[0],
                    'model' => $data[1],
                    'partner_category' => $data[2],
                    'year' => 2022,
                    'status' => Vehicle::STATUS_AVAILABLE,
                    'daily_price' => 250,
                    'deposit' => 2000,
                    'insurance_annual_cost' => 3000,
                    'insurance_start_date' => now()->subYear(),
                    'insurance_end_date' => now()->addDays(7),
                    'insurance_reminder' => true,
                ]);
            }
            $vehicles[] = $v;
        }
        $vehicleExpiring = $vehicles[0];
        $vehicleNormal = $vehicles[1] ?? $vehicles[0];

        $customers = [];
        foreach ([
            ['Ahmed Bennani', 'AB123456', '0612345678', 'ahmed@test.ma'],
            ['Fatima Alaoui', 'CD789012', '0698765432', 'fatima@test.ma'],
        ] as $i => $data) {
            $c = $company->customers()->firstOrCreate(
                ['cin' => $data[1]],
                ['name' => $data[0], 'phone' => $data[2], 'email' => $data[3], 'city' => 'Casablanca']
            );
            $customers[] = $c;
        }
        $customer = $customers[0];

        $template = $company->contractTemplates()->firstOrCreate(
            ['name' => 'Contrat type'],
            [
                'slug' => 'contrat-type',
                'content' => '<h1>Contrat de location</h1><p>Client: {{client_name}}</p><p>Véhicule: {{vehicle_plate}}</p><p>Du {{rental_start_date}} au {{rental_end_date}}</p><p>Montant: {{total_amount}} MAD. Caution: {{deposit_amount}} MAD.</p>',
                'variables' => [],
            ]
        );
        if (! $company->default_contract_template_id) {
            $company->update(['default_contract_template_id' => $template->id]);
        }

        $today = Carbon::today();
        $tomorrow = $today->copy()->addDay();
        $nextWeek = $today->copy()->addDays(7);

        $res1 = $company->reservations()->firstOrCreate(
            ['reference' => 'RES-' . $today->format('Ymd') . '-001'],
            [
                'vehicle_id' => $vehicleNormal->id,
                'customer_id' => $customer->id,
                'pickup_branch_id' => $branch1->id,
                'return_branch_id' => $branch1->id,
                'status' => Reservation::STATUS_CONFIRMED,
                'payment_status' => Reservation::PAYMENT_PAID,
                'start_at' => $today->copy()->setTime(9, 0),
                'end_at' => $today->copy()->setTime(18, 0),
                'total_price' => 250,
                'confirmed_at' => now()->subDay(),
            ]
        );
        $res1->payments()->firstOrCreate(
            ['type' => ReservationPayment::TYPE_RENTAL],
            [
                'branch_id' => $branch1->id,
                'amount' => 250,
                'method' => ReservationPayment::METHOD_CASH,
                'paid_at' => $today,
                'reference' => 'PAY-001',
            ]
        );
        $res1->reservationContract()->firstOrCreate(
            [],
            [
                'contract_template_id' => $template->id,
                'snapshot_html' => $template->renderForReservation($res1),
                'status' => ReservationContract::STATUS_GENERATED,
                'generated_at' => now(),
            ]
        );
        $inspectionOut = $res1->inspections()->firstOrCreate(
            ['type' => ReservationInspection::TYPE_OUT],
            [
                'inspected_at' => $today,
                'mileage' => 15000,
                'fuel_level' => 'plein',
                'notes' => 'État correct.',
            ]
        );
        $inspectionOut->photos()->firstOrCreate(
            ['path' => 'inspections/' . $inspectionOut->id . '/sample.jpg'],
            ['caption' => 'Photo avant']
        );

        $res2 = $company->reservations()->firstOrCreate(
            ['reference' => 'RES-' . $today->format('Ymd') . '-002'],
            [
                'vehicle_id' => $vehicleExpiring->id,
                'customer_id' => ($customers[1] ?? $customer)->id,
                'pickup_branch_id' => $branch1->id,
                'return_branch_id' => $branch1->id,
                'status' => Reservation::STATUS_CONFIRMED,
                'payment_status' => Reservation::PAYMENT_UNPAID,
                'start_at' => $nextWeek->copy()->setTime(10, 0),
                'end_at' => $nextWeek->copy()->addDays(2)->setTime(10, 0),
                'total_price' => 500,
                'confirmed_at' => now(),
            ]
        );

        $res3 = $company->reservations()->firstOrCreate(
            ['reference' => 'RES-' . $today->format('Ymd') . '-003'],
            [
                'vehicle_id' => $vehicleNormal->id,
                'customer_id' => $customer->id,
                'pickup_branch_id' => $branch1->id,
                'return_branch_id' => $branch1->id,
                'status' => Reservation::STATUS_DRAFT,
                'payment_status' => Reservation::PAYMENT_UNPAID,
                'start_at' => $tomorrow->copy()->setTime(9, 0),
                'end_at' => $tomorrow->copy()->addDays(3)->setTime(9, 0),
                'total_price' => 750,
            ]
        );
    }

    private function seedSecondCompanyData(Company $company): void
    {
        $branch = $company->branches()->first();
        if (! $branch) {
            return;
        }
        $v = $company->vehicles()->firstOrCreate(
            ['plate' => 'XX-99999'],
            [
                'branch_id' => $branch->id,
                'brand' => 'Hyundai',
                'model' => 'i20',
                'year' => 2023,
                'status' => Vehicle::STATUS_AVAILABLE,
                'daily_price' => 280,
            ]
        );
        $c = $company->customers()->firstOrCreate(
            ['cin' => 'XX999999'],
            ['name' => 'Other Client', 'phone' => '0600000000', 'city' => 'Rabat']
        );
        $company->reservations()->firstOrCreate(
            ['reference' => 'RES-OC-001'],
            [
                'vehicle_id' => $v->id,
                'customer_id' => $c->id,
                'pickup_branch_id' => $branch->id,
                'return_branch_id' => $branch->id,
                'status' => Reservation::STATUS_CONFIRMED,
                'payment_status' => Reservation::PAYMENT_UNPAID,
                'start_at' => now()->addDays(5),
                'end_at' => now()->addDays(7),
                'total_price' => 560,
                'confirmed_at' => now(),
            ]
        );
    }
}
