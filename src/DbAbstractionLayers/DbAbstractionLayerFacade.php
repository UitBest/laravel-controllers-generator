<?php

declare(strict_types=1);

namespace TimoCuijpers\LaravelControllersGenerator\DbAbstractionLayers;

use TimoCuijpers\LaravelControllersGenerator\Contracts\DbAbstractionLayerInterface;
use TimoCuijpers\LaravelControllersGenerator\Exceptions\DbAbstractionLayerNotFound;

class DbAbstractionLayerFacade
{
    /**
     * @throws DbAbstractionLayerNotFound
     */
    public static function instance(): DbAbstractionLayerInterface
    {
        throw new DbAbstractionLayerNotFound;
    }
}
