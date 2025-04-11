<?php

declare(strict_types=1);

namespace TimoCuijpers\LaravelControllersGenerator\Writers;

use TimoCuijpers\LaravelControllersGenerator\Entities\Entity;

abstract class Writer implements WriterInterface
{
    public string $spacer = '    ';

    public bool $prevElementWasNotEmpty = false;

    public function __construct(public string $className, public Entity $entity, public string $stubContent, protected bool $isBase = false) {}

    public function writeModelFile(): string
    {
        $search = [
            '{{strict}}',
            '{{namespace}}',
            '{{abstract}}',
            '{{className}}',
            '{{imports}}',
            '{{parent}}',
            '{{body}}',
        ];
        $replace = [
            $this->strict(),
            $this->namespace(),
            $this->abstract(),
            $this->className,
            $this->imports(),
            $this->parent(),
            $this->body(),
        ];

        return str_replace($search, $replace, $this->stubContent);
    }

    abstract public function traits(): string;

    abstract public function abstract(): string;

    abstract public function imports(): string;

    abstract public function parent(): string;

    public function namespace(): string
    {
        return $this->entity->namespace ?? (string) config('controllers-generator.namespace', 'App\Models');
    }

    public function strict(): string
    {
        return config('controllers-generator.strict_types', true) ? "\n".'declare(strict_types=1);'."\n" : '';
    }

    public function body(): string
    {
        return $this->traits().$this->table();
    }

    public function table(): string
    {
        if ($this->entity->showTableProperty) {
            $content = '';
            if ($this->prevElementWasNotEmpty) {
                $content = "\n";
            }

            $this->prevElementWasNotEmpty = true;

            return $content."\n".$this->spacer.'protected $table = \''.$this->entity->name.'\';'."\n";
        }

        $this->prevElementWasNotEmpty = false;

        return '';
    }
}
