<?php

declare(strict_types=1);

namespace TimoCuijpers\LaravelControllersGenerator\Writers\Laravel12;

use TimoCuijpers\LaravelControllersGenerator\Writers\WriterInterface;

class Writer extends \TimoCuijpers\LaravelControllersGenerator\Writers\Writer implements WriterInterface
{
    public function imports(): string
    {
        asort($this->entity->imports);

        return implode("\n", array_map(function (string $import) {
            return "use $import;";
        }, array_unique($this->entity->imports)));
    }

    public function parent(): string
    {
        $parent = $this->entity->parent ?? 'Controller';

        if (count($this->entity->interfaces) > 0) {
            $this->prevElementWasNotEmpty = true;

            asort($this->entity->interfaces);

            $parent .= ' implements '.implode(', ', array_map(function (string $interface) {
                $parts = explode('\\', $interface);

                return end($parts);
            }, $this->entity->interfaces));

            return $parent;
        }

        $this->prevElementWasNotEmpty = false;

        return $parent;
    }

    public function traits(): string
    {
        $traitsToUse = $this->entity->traits;
        if ($this->entity->softDeletes) {
            $traitsToUse[] = 'SoftDeletes';
        }
        if (count($traitsToUse) > 0) {
            $this->prevElementWasNotEmpty = true;

            asort($traitsToUse);

            $body = '';
            foreach ($traitsToUse as $trait) {
                $parts = explode('\\', $trait);
                $body .= "\n".$this->spacer.'use '.end($parts).';';
            }

            // $body .= "\n";

            return $body;
        }

        $this->prevElementWasNotEmpty = false;

        return '';
    }

    public function abstract(): string
    {
        return $this->entity->abstract ? 'abstract ' : '';
    }
}
