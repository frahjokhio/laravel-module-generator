<?php
namespace Frahjokhio\ModuleGenerator\Providers;

use Frahjokhio\ModuleGenerator\Commands\DeleteModuleCommand;
use Illuminate\Support\ServiceProvider;
use Frahjokhio\ModuleGenerator\Commands\MakeModuleCommand;

class ModuleGeneratorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeModuleCommand::class,
                DeleteModuleCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/../Stubs' => base_path('stubs/module-generator'),
            ], 'module-stubs');
        }
    }
}
