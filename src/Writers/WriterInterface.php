<?php

declare(strict_types=1);

namespace TimoCuijpers\LaravelControllersGenerator\Writers;

interface WriterInterface
{
    public function writeModelFile(): string;

    public function namespace(): string;

    public function parent(): string;

    public function traits(): string;

    public function abstract(): string;

    public function table(): string;

    public function imports(): string;
}
