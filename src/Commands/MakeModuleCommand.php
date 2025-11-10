<?php

namespace Frahjokhio\ModuleGenerator\Commands;

use Frahjokhio\ModuleGenerator\Service\ModuleGeneratorService;
use Illuminate\Console\Command;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module {name : Module name}';
    protected $description = 'Generate a new module with full structure, migration, and seeder.';

    public function handle(ModuleGeneratorService $generator)
    {
        $name = $this->argument('name');

        $generator->generate($name, $this);

        $this->info("✅ Module {$name} created successfully.");
        return Command::SUCCESS;
    }
}
