<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator;

use GiacomoMasseroni\LaravelControllersGenerator\Commands\LaravelControllersGeneratorCommand;
use GiacomoMasseroni\LaravelModelsGenerator\Commands\LaravelModelsGeneratorAliasCommand;
use GiacomoMasseroni\LaravelModelsGenerator\Commands\LaravelModelsGeneratorCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelControllersGeneratorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */

        $package
            ->name('laravel-controllers-generator')
            ->hasConfigFile()
            ->hasCommand(LaravelControllersGeneratorCommand::class);
    }
}
