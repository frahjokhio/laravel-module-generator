<?php

namespace Frahjokhio\ModuleGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DeleteModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:module {name?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Completely remove a module, its files, migration, and seeder.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $rawName = $this->argument('name') ?? $this->ask('Enter module name to delete (e.g. Post)');
        
        // Use Studly case for folder name consistency
        $name = Str::studly($rawName);

        $modulePath = app_path("Modules/{$name}");

        if (!File::exists($modulePath)) {
            $this->error("❌ Module '{$name}' does not exist in app/Modules.");
            return;
        }

        if (! $this->confirm("⚠️ Are you sure you want to delete module '{$name}'? This cannot be undone.", false)) {
            $this->info('Deletion cancelled.');
            return;
        }

        // 1. Remove Service Provider entry first
        $this->removeServiceProvider($name);

        // 2. Remove migration files
        $this->deleteMigrations($name);

        // 3. Remove seeder if exists
        $this->deleteSeeder($name);
        
        // 4. Delete module folder
        File::deleteDirectory($modulePath);
        $this->info("🗑️ Deleted: app/Modules/{$name} folder.");

        $this->info("✅ Module '{$name}' cleanup complete.");
    }

    /**
     * Removes the module's Service Provider from bootstrap/providers.php.
     */
    protected function removeServiceProvider(string $name): void
    {
        $providerClass = "App\\Modules\\{$name}\\{$name}ServiceProvider::class";
        $providerPath = base_path('bootstrap/providers.php');

        if (!File::exists($providerPath)) {
            $this->warn("bootstrap/providers.php not found — skipping provider removal.");
            return;
        }

        $content = File::get($providerPath);
        
        // Use preg_quote to correctly escape the backslashes in the provider class string
        $escapedProviderClass = preg_quote($providerClass, '/');

        // Pattern to match the line, including any surrounding whitespace/newlines
        $pattern = "/\s*{$escapedProviderClass},\s*/";
        $updated = preg_replace($pattern, "\n", $content, 1);

        File::put($providerPath, $updated);

        $this->info("🧹 Removed {$name}ServiceProvider from bootstrap/providers.php");
    }

    /**
     * Deletes associated migration files based on the module name.
     */
    protected function deleteMigrations(string $name): void
    {
        $migrationPath = database_path('migrations');
        $lowerTable = Str::snake(Str::plural(Str::lower($name)));
        $searchFragment = "create_{$lowerTable}_table";
        
        $files = File::files($migrationPath);

        foreach ($files as $file) {
            if (str_contains($file->getFilename(), $searchFragment)) {
                File::delete($file->getRealPath());
                $this->info("🗑️ Deleted migration: {$file->getFilename()}");
            }
        }
    }

    /**
     * Deletes the module's seeder file.
     */
    protected function deleteSeeder(string $name): void
    {
        $seederFile = database_path("seeders/{$name}Seeder.php");

        if (File::exists($seederFile)) {
            File::delete($seederFile);
            $this->info("🗑️ Deleted seeder: {$name}Seeder.php");
        }
    }
}