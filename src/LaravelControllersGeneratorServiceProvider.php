<?php

declare(strict_types=1);

namespace TimoCuijpers\LaravelControllersGenerator;

use TimoCuijpers\LaravelControllersGenerator\Commands\LaravelControllersGeneratorCommand;
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
