<?php

declare(strict_types=1);

namespace TimoCuijpers\LaravelControllersGenerator\Helpers;

use Illuminate\Support\Str;
use TimoCuijpers\LaravelControllersGenerator\Enums\RelationshipsNameCaseTypeEnum;

class NamingHelper
{
    public static function caseRelationName(string $name): string
    {
        return match (config('controllers-generator.relationships_name_case_type')) {
            RelationshipsNameCaseTypeEnum::SNAKE_CASE => Str::snake($name),
            default => Str::camel($name),
        };
    }
}
