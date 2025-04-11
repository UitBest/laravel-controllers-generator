<?php

declare(strict_types=1);

namespace TimoCuijpers\LaravelControllersGenerator\Entities\Relationships;

use TimoCuijpers\LaravelControllersGenerator\Contracts\RelationshipInterface;

class HasMany implements RelationshipInterface
{
    public string $name;

    public function __construct(
        public string $related,
        public string $foreignKeyName,
        public ?string $localKeyName = null
    ) {
        $this->name = $related;
    }
}
