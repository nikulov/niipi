<?php

namespace App\Console\Commands;

use App\Models\Concerns\TracksMediaUsage;
use App\Models\MediaFile;
use App\Services\MediaUsageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MediaSyncCommand extends Command
{
    protected $signature = 'media:sync {--usages-only : Only rebuild usages, skip file scan}';

    protected $description = 'Scan storage and index files into media library, then rebuild usages';

    private const SKIP_PREFIXES = ['livewire-tmp/', 'forms/'];

    public function handle(MediaUsageService $service): int
    {
        if (! $this->option('usages-only')) {
            $this->scanFiles($service);
            $this->cleanOrphans();
        }

        $this->rebuildUsages($service);

        $this->info('Done.');

        return self::SUCCESS;
    }

    private function scanFiles(MediaUsageService $service): void
    {
        $this->info('Scanning storage/app/public...');

        $disk = Storage::disk('public');
        $files = $disk->allFiles();
        $bar = $this->output->createProgressBar(count($files));
        $created = 0;

        foreach ($files as $path) {
            if ($this->shouldSkipPath($path)) {
                $bar->advance();

                continue;
            }

            $existing = MediaFile::where('path', $path)->where('disk', 'public')->first();

            if (! $existing && $service->registerFile($path)) {
                $created++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Indexed {$created} new files.");
    }

    private function shouldSkipPath(string $path): bool
    {
        if (str_starts_with(basename($path), '.')) {
            return true;
        }

        foreach (self::SKIP_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function cleanOrphans(): void
    {
        $this->info('Cleaning orphaned records...');

        $removed = 0;
        MediaFile::where('disk', 'public')->chunk(200, function ($files) use (&$removed): void {
            foreach ($files as $file) {
                if (! $file->existsOnDisk()) {
                    $file->usages()->delete();
                    $file->delete();
                    $removed++;
                }
            }
        });

        $this->info("Removed {$removed} orphaned records.");
    }

    private function rebuildUsages(MediaUsageService $service): void
    {
        $this->info('Rebuilding usages...');

        foreach ($this->getTrackedModelClasses() as $class) {
            $this->info("  Processing {$class}...");

            $keyName = (new $class)->getKeyName();

            $class::query()
                ->orderBy($keyName)
                ->chunk(100, function ($models) use ($service): void {
                    foreach ($models as $model) {
                        $service->syncForModel($model);
                    }
                });
        }
    }

    private function getTrackedModelClasses(): array
    {
        $models = [];
        $files = File::allFiles(app_path('Models'));

        foreach ($files as $file) {
            if (str_starts_with($file->getRelativePath(), 'Concerns')) {
                continue;
            }

            $class = 'App\\Models\\'.str_replace(
                ['/', '.php'],
                ['\\', ''],
                $file->getRelativePathname()
            );

            if (! class_exists($class)) {
                continue;
            }

            if (in_array(TracksMediaUsage::class, class_uses_recursive($class), true)) {
                $models[] = $class;
            }
        }

        return $models;
    }
}
