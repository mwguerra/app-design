<?php

namespace MWGuerra\AppDesign;

use Illuminate\Support\ServiceProvider;
use MWGuerra\AppDesign\Commands\AddComposerScript;
use MWGuerra\AppDesign\Commands\GitCommit;
use MWGuerra\AppDesign\Commands\InstallDependencies;
use MWGuerra\AppDesign\Commands\ProcessResources;

class AppDesignServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('app-design', function () {
            return new AppDesign();
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GitCommit::class,
                InstallDependencies::class,
                AddComposerScript::class,
                ProcessResources::class
            ]);
        }
        $this->mergeConfigFrom(__DIR__ . '/../config/appdesign.php', 'appdesign');
    }
}
