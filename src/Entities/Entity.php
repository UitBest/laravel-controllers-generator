<?php

declare(strict_types=1);

namespace TimoCuijpers\LaravelControllersGenerator\Entities;

class Entity
{
    /** @var array<string> */
    public array $imports = [];

    public bool $abstract = false;

    public ?string $parent = null;

    /** @var array<string> */
    public array $interfaces = [];

    /** @var array<string> */
    public array $traits = [];

    public ?bool $showTableProperty = null;

    public bool $softDeletes = false;

    public ?string $namespace = null;

    public function __construct(public string $name, public string $className)
    {
        /** @var array<string> $parts */
        $parts = explode('\\', (string) config('controllers-generator.parent', 'Model'));
        $this->parent = $parts ? end($parts) : 'Model';
        $this->interfaces = (array) config('controllers-generator.interfaces', []);
        $this->traits = (array) config('controllers-generator.traits', []);
        $this->showTableProperty = (bool) config('controllers-generator.table', false);
        $this->className = (string) implode(array_map('ucfirst', explode('.', $this->className)));
    }

    public function importLaravelModel(): bool
    {
        return ! str_contains($this->parent ?? '', 'Base');
    }

    public function cleanForBase(): void
    {
        $this->interfaces = [];
        $this->showTableProperty = false;
        $this->parent = 'Base'.$this->className;
        $this->abstract = false;
        $this->namespace = (string) config('controllers-generator.namespace', 'App\Models');
        $this->imports = [$this->namespace.'\\Base\\'.$this->className.' as Base'.$this->className];
    }
}
