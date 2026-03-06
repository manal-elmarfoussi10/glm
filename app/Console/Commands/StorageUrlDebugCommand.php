<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class StorageUrlDebugCommand extends Command
{
    protected $signature = 'storage:debug
                            {--vehicle= : Vehicle ID to test (default: first with image_path)}';

    protected $description = 'Debug storage and asset URLs: symlink, disk config, sample vehicle image URL, file existence.';

    public function handle(): int
    {
        $this->info('=== Storage / asset URL debug ===');
        $this->newLine();

        $this->checkSymlink();
        $this->checkConfig();
        $this->checkVehicleImage();
        $this->checkLogo();

        return self::SUCCESS;
    }

    private function checkSymlink(): void
    {
        $this->info('1. Public storage symlink');
        $link = public_path('storage');
        $target = storage_path('app/public');
        if (file_exists($link)) {
            if (is_link($link)) {
                $readlink = readlink($link);
                $this->line("   <fg=green>Exists</> (symlink)");
                $this->line("   public/storage → " . ($readlink ?: '?'));
                if ($readlink && realpath($readlink) !== realpath($target)) {
                    $this->warn("   Expected target: {$target}");
                }
            } else {
                $this->warn("   public/storage exists but is NOT a symlink (directory?). Run: php artisan storage:link");
            }
        } else {
            $this->error("   public/storage does not exist. Run: php artisan storage:link");
        }
        $this->newLine();
    }

    private function checkConfig(): void
    {
        $this->info('2. Config');
        $this->line('   APP_URL: ' . (config('app.url') ?: '(empty)'));
        $this->line('   ASSET_URL: ' . (config('app.asset_url') ?: '(empty)'));
        $this->line('   Default disk: ' . config('filesystems.default'));
        $disk = config('filesystems.disks.public');
        $this->line('   Public disk root: ' . ($disk['root'] ?? 'N/A'));
        $this->line('   Public disk URL: ' . ($disk['url'] ?? 'N/A'));
        $this->newLine();
    }

    private function checkVehicleImage(): void
    {
        $this->info('3. Sample vehicle image');
        $vehicleId = $this->option('vehicle');
        $vehicle = $vehicleId
            ? Vehicle::find($vehicleId)
            : Vehicle::whereNotNull('image_path')->where('image_path', '!=', '')->first();

        if (! $vehicle) {
            $this->warn('   No vehicle with image_path found. Add a vehicle photo first.');
            $this->newLine();
            return;
        }

        $path = $vehicle->image_path;
        $url = storage_public_url($path);
        $exists = storage_public_exists($path);
        $existsOnDisk = $path && Storage::disk('public')->exists($path);

        $this->line("   Vehicle ID: {$vehicle->id} ({$vehicle->plate})");
        $this->line("   image_path (DB): " . ($path ?: '(null)'));
        $this->line("   Generated URL: " . ($url ?: '(null)'));
        $this->line("   storage_public_exists(): " . ($exists ? 'yes' : 'no'));
        $this->line("   Storage::disk('public')->exists(): " . ($existsOnDisk ? 'yes' : 'no'));
        if (! $existsOnDisk && $path) {
            $this->warn('   File is missing on disk. Re-upload the vehicle image or fix path.');
        }
        $this->newLine();
    }

    private function checkLogo(): void
    {
        $this->info('4. Logo (static asset)');
        $logoPath = 'images/light-logo.png';
        $url = app_asset($logoPath);
        $fullPath = public_path($logoPath);
        $this->line("   Path: {$logoPath}");
        $this->line("   app_asset() URL: {$url}");
        $this->line("   File exists in public/: " . (file_exists($fullPath) ? 'yes' : 'no'));
        $this->newLine();
    }
}
