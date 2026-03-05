<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $plans = DB::table('plans')->get();
        foreach ($plans as $plan) {
            $fl = json_decode($plan->features_limits ?? '{}', true) ?: [];
            $features = $fl['features'] ?? [];
            $name = strtolower($plan->name ?? '');
            $features['partner_availability'] = ! str_contains($name, 'starter');
            $fl['features'] = $features;
            DB::table('plans')->where('id', $plan->id)->update(['features_limits' => json_encode($fl)]);
        }
    }

    public function down(): void
    {
        $plans = DB::table('plans')->get();
        foreach ($plans as $plan) {
            $fl = json_decode($plan->features_limits ?? '{}', true) ?: [];
            $features = $fl['features'] ?? [];
            unset($features['partner_availability']);
            $fl['features'] = $features;
            DB::table('plans')->where('id', $plan->id)->update(['features_limits' => json_encode($fl)]);
        }
    }
};
