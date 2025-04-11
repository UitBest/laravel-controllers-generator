<?php

declare(strict_types=1);

namespace TimoCuijpers\LaravelControllersGenerator\Entities;

class PrimaryKey
{
    public function __construct(
        public string $name,
        public bool $autoIncrement,
        public ?string $keyType,
    ) {}
}
