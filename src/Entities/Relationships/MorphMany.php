<?php

declare(strict_types=1);

namespace TimoCuijpers\LaravelControllersGenerator\Entities\Relationships;

use TimoCuijpers\LaravelControllersGenerator\Contracts\RelationshipInterface;

class MorphMany implements RelationshipInterface
{
    public function __construct(public string $name, public string $related, public string $type) {}
}
