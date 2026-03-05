<?php

namespace App\Console\Commands;

use App\Services\PartnerAvailabilityCacheService;
use Illuminate\Console\Command;

class BuildPartnerAvailabilityCacheCommand extends Command
{
    protected $signature = 'partner-availability:cache {--company= : Rebuild only for this company ID}';

    protected $description = 'Rebuild partner availability cache (daily/hourly recommended)';

    public function handle(PartnerAvailabilityCacheService $service): int
    {
        $companyId = $this->option('company') ? (int) $this->option('company') : null;
        $count = $service->rebuildForAll($companyId);
        $this->info("Rebuilt cache for {$count} companies.");
        return self::SUCCESS;
    }
}
