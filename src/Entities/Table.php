<?php

declare(strict_types=1);

namespace TimoCuijpers\LaravelControllersGenerator\Entities;

class Table extends Entity
{
    public function importLaravelModel(): bool
    {
        return ! str_contains($this->parent ?? '', 'Base');
    }
}
