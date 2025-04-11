<?php

declare(strict_types=1);

namespace TimoCuijpers\LaravelControllersGenerator\Commands;

use Illuminate\Console\Command;

class LaravelControllersGeneratorAliasCommand extends Command
{
    public $signature = 'controllers:generate';

    protected $description = 'Alias for laravel-controllers-generator:generate command';

    public function handle()
    {
        $this->call('laravel-controllers-generator:generate');
    }
}
