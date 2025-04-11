<?php

declare(strict_types=1);

namespace TimoCuijpers\LaravelControllersGenerator\Entities\Relationships;

use TimoCuijpers\LaravelControllersGenerator\Contracts\RelationshipInterface;

class BelongsToMany implements RelationshipInterface
{
    public string $name;

    public string $foreignClassName;

    public bool $timestamps = false;

    /**
     * @param  array<string>  $pivotAttributes
     */
    public function __construct(
        public string $related,
        public string $pivot,
        public ?string $foreignPivotKey = null,
        public ?string $relatedPivotKey = null,
        public ?string $parentKey = null,   // TODO: what is it?
        public ?string $relatedKey = null,  // TODO: what is it?
        public ?string $relation = null,    // TODO: what is it?
        public array $pivotAttributes = [],
    ) {}
}
