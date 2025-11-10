<?php

namespace Frahjokhio\ModuleGenerator\Service;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Console\Command;

class ModuleGeneratorService
{
    public function generate(string $name, Command $command): void
    {
        $studly = Str::studly($name);
        $lower = Str::lower($name);
        $table = Str::plural($lower);

        $basePath = app_path("Modules/{$studly}");
        $this->createFolders($basePath);
        $columnsArray = [];

        $command->info("🚀 Creating module structure for {$studly}...");

        if ($command->confirm('Do you want to create a migration?', true)) {
            $columnsInput = trim($command->ask('Enter columns (e.g. title:string, content:text, published_at:datetime) or leave empty', ''));
            $columnsArray = $columnsInput ? $this->parseColumns($columnsInput) : [];
            $this->createMigration($table, $columnsArray);
        }

        $this->createBaseFiles($basePath, $studly, $columnsArray);

        if ($command->confirm('Do you want to create a seeder?', false)) {
            $command->call('make:seeder', ['name' => "{$studly}Seeder"]);
        }

        $this->registerServiceProvider($studly, $command);

        $command->info("\n📦 Module '{$studly}' created successfully with:");
        $command->line("- Folder: app/Modules/{$studly}");
        if (!empty($columnsArray)) {
            $command->line("- Migration columns: " . implode(', ', array_keys($columnsArray)));
        }
        $command->line("- ServiceProvider: registered in bootstrap/providers.php");
        $command->line("- CRUD files: Controller, Model, Repository, Service, Request, routes/api.php");
    }

    protected function createFolders(string $basePath): void
    {
        $folders = ['Controllers', 'Models', 'Requests', 'Services', 'Repositories', 'routes'];
        foreach ($folders as $folder) {
            File::ensureDirectoryExists("{$basePath}/{$folder}");
        }
    }

    protected function parseColumns(string $input): array
    {
        $columns = [];
        $pairs = array_map('trim', explode(',', $input));

        foreach ($pairs as $pair) {
            $parts = array_map('trim', explode(':', $pair));
            $column = $parts[0];
            $type = $parts[1] ?? 'string';
            $modifiers = array_slice($parts, 2);

            if ($type === 'enum' && isset($modifiers[0])) {
                $values = explode('|', $modifiers[0]);
                $columns[$column] = [
                    'type' => 'enum',
                    'values' => $values,
                    'modifiers' => array_slice($modifiers, 1),
                ];
            } else {
                $columns[$column] = ['type' => $type, 'modifiers' => $modifiers];
            }
        }

        return $columns;
    }

    protected function createMigration(string $table, array $columns): void
    {
        $migrationName = "create_{$table}_table";
        \Artisan::call('make:migration', ['name' => $migrationName, '--create' => $table]);

        $latestMigration = collect(File::files(database_path('migrations')))
            ->sortByDesc(fn($f) => $f->getCTime())
            ->first();

        if (!$latestMigration) return;

        if (!empty($columns)) {
            $content = File::get($latestMigration->getRealPath());
            $schemaLines = '';

            foreach ($columns as $col => $meta) {
                $line = $meta['type'] === 'enum'
                    ? "\$table->enum('{$col}', ['" . implode("','", $meta['values']) . "'])"
                    : "\$table->{$meta['type']}('{$col}')";

                foreach ($meta['modifiers'] as $mod) {
                    if (!$mod) continue;
                    $line .= preg_match('/\w+\(.*\)/', $mod) ? "->{$mod}" : "->{$mod}()";
                }

                $schemaLines .= "            {$line};\n";
            }

            $content = str_replace(
                "\$table->timestamps();",
                $schemaLines . "            \$table->timestamps();",
                $content
            );

            File::put($latestMigration->getRealPath(), $content);
        }
    }

    protected function createBaseFiles(string $basePath, string $name, array $columns): void
    {
        $stubsPath = __DIR__ . '/../Stubs';

        $this->writeFile("{$basePath}/routes/api.php", $this->renderStub("{$stubsPath}/routes.stub", ['name' => $name]));
        $this->writeFile("{$basePath}/Controllers/{$name}Controller.php", $this->renderStub("{$stubsPath}/controller.stub", ['name' => $name]));
        $this->writeFile("{$basePath}/Services/{$name}Service.php", $this->renderStub("{$stubsPath}/service.stub", ['name' => $name]));
        $this->writeFile("{$basePath}/Repositories/{$name}Repository.php", $this->renderStub("{$stubsPath}/repository.stub", ['name' => $name]));
        $this->writeFile("{$basePath}/Requests/{$name}Request.php", $this->renderStub("{$stubsPath}/request.stub", ['name' => $name]));
        $this->writeFile("{$basePath}/Models/{$name}.php", $this->renderStub("{$stubsPath}/model.stub", [
            'name' => $name,
            'fillable' => !empty($columns)
                ? "'" . implode("', '", array_keys($columns)) . "'"
                : ''
        ]));
        $this->writeFile("{$basePath}/{$name}ServiceProvider.php", $this->renderStub("{$stubsPath}/provider.stub", ['name' => $name]));
    }

    protected function renderStub(string $stubPath, array $vars = []): string
    {
        $content = File::get($stubPath);
        foreach ($vars as $key => $value) {
            $content = str_replace('{{ ' . $key . ' }}', $value, $content);
        }
        return $content;
    }

    protected function writeFile(string $path, string $content): void
    {
        File::put($path, $content);
    }

    protected function registerServiceProvider(string $name, Command $command): void
    {
        $providerClass = "App\\Modules\\{$name}\\{$name}ServiceProvider::class";
        $providerPath = base_path('bootstrap/providers.php');
        $content = File::get($providerPath);

        if (strpos($content, $providerClass) !== false) {
            $command->info("ℹ️ {$name}ServiceProvider already registered.");
            return;
        }

        $content = preg_replace(
            '/\];\s*$/',
            "    {$providerClass},\n];",
            $content
        );

        File::put($providerPath, $content);
        $command->info("✅ {$name}ServiceProvider added to bootstrap/providers.php");
    }
}
