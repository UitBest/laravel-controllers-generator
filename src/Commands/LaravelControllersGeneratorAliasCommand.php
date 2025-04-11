<?php

declare(strict_types=1);

namespace TimoCuijpers\LaravelControllersGenerator\Commands;

use Illuminate\Console\Command;

class LaravelControllersGeneratorAliasCommand extends Command
{
    public $signature = 'controllers:generate
                        {--s|schema= : The name of the database}
                        {--c|connection= : The name of the connection}
                        {--t|table= : The name of the table}';

    protected $description = 'Alias for laravel-controllers-generator:generate command';

    public function handle()
    {
        $this->call('laravel-controllers-generator:generate', [
            '--schema' => $this->option('schema'),
            '--connection' => $this->option('connection'),
            '--table' => $this->option('table'),
        ]);
    }
}
