<?php

declare(strict_types=1);

namespace TimoCuijpers\LaravelControllersGenerator;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use TimoCuijpers\LaravelControllersGenerator\Commands\LaravelControllersGeneratorAliasCommand;
use TimoCuijpers\LaravelControllersGenerator\Commands\LaravelControllersGeneratorCommand;

class LaravelControllersGeneratorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */

        // Check if the alias command is enabled in the config
        if (config('controllers-generator.enable_alias')) {
            $package
                ->name('laravel-controllers-generator')
                ->hasConfigFile()
                ->hasCommand(LaravelControllersGeneratorCommand::class)
                ->hasCommand(LaravelControllersGeneratorAliasCommand::class);
        } else {
            $package
                ->name('laravel-controllers-generator')
                ->hasConfigFile()
                ->hasCommand(LaravelControllersGeneratorCommand::class);
        }
    }
}
