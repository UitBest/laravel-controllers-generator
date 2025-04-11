<?php

declare(strict_types=1);

namespace TimoCuijpers\LaravelControllersGenerator\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \TimoCuijpers\LaravelControllersGenerator\LaravelControllersGenerator
 */
class LaravelControllersGenerator extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \TimoCuijpers\LaravelControllersGenerator\LaravelControllersGenerator::class;
    }
}
