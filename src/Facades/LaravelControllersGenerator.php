<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \GiacomoMasseroni\LaravelModelsGenerator\LaravelModelsGenerator
 */
class LaravelControllersGenerator extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \GiacomoMasseroni\LaravelModelsGenerator\LaravelControllersGenerator::class;
    }
}
