<?php

declare(strict_types=1);

namespace TimoCuijpers\LaravelControllersGenerator\Concerns;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\BigIntType;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\DBAL\Types\DateImmutableType;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\DateTimeTzImmutableType;
use Doctrine\DBAL\Types\DateTimeTzType;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\DecimalType;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\SmallFloatType;
use Doctrine\DBAL\Types\SmallIntType;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\TextType;
use Doctrine\DBAL\Types\Type;
use TimoCuijpers\LaravelControllersGenerator\Contracts\DriverConnectorInterface;
use TimoCuijpers\LaravelControllersGenerator\Entities\Entity;
use TimoCuijpers\LaravelControllersGenerator\Entities\Table;
use TimoCuijpers\LaravelControllersGenerator\Enums\ColumnTypeEnum;

/**
 * @mixin DriverConnectorInterface
 */
trait DBALable
{
    private AbstractSchemaManager $sm;

    private Connection $conn;

    /**
     * @var array<string, mixed>
     */
    private static array $entityColumns = [];

    /**
     * @var array<string, mixed>
     */
    private static array $entityIndexes = [];

    /**
     * @var array<string, string>
     */
    private array $typeColumnPropertyMaps = [
        'datetime' => 'Carbon',
    ];

    /**
     * @throws Exception
     */
    public function listTables(): array
    {
        return $this->getTables($this->sm->listTables());
    }

    /**
     * @param  list<\Doctrine\DBAL\Schema\Table>  $tables
     *
     * @return array<string, Table>
     *
     * @throws Exception
     */
    private function getTables(array $tables): array
    {
        /** @var array<string, Table> $dbTables */
        $dbTables = [];

        foreach ($tables as $table) {
            $columns = $this->getEntityColumns($table->getName());
            $dbTable = new Table($table->getName(), dbEntityNameToModelName($table->getName()));

            /** @var Column $column */
            foreach ($columns as $column) {
                // TODO: Add $rules

                $laravelColumnType = $this->laravelColumnType($this->mapColumnType($column->getType()), $dbTable);

                $fieldType = match ($laravelColumnType) {
                    'integer', 'float' => 'numeric',
                    'boolean' => 'boolean',
                    default => 'string',
                };

                $rules[$column->getName()][] = $column->getNotnull() ? 'required' : 'nullable';
                $rules[$column->getName()][] = 'size:'.($column->getLength() ?? $column->getPrecision());
                $rules[$column->getName()][] = $fieldType;

                if ($fieldType === 'numeric') {
                    $rules[$column->getName()][] = 'max_digits:'.$column->getScale();
                    $rules[$column->getName()][] = 'min_digits:'.($column->getFixed() ? $column->getScale() : '0');
                }
            }

            $dbTables[$table->getName()] = $dbTable;
        }

        return $dbTables;
    }

    public function laravelColumnTypeForCast(ColumnTypeEnum $type, ?Entity $dbTable = null): string
    {
        return match ($type) {
            ColumnTypeEnum::INT => 'integer',
            ColumnTypeEnum::DATETIME => 'datetime',
            ColumnTypeEnum::FLOAT => 'float',
            ColumnTypeEnum::BOOLEAN => 'boolean',
            default => 'string',
        };
    }

    public function laravelColumnType(ColumnTypeEnum $type, ?Entity $dbTable = null): string
    {
        if ($type == ColumnTypeEnum::DATETIME) {
            if ($dbTable !== null) {
                $dbTable->imports[] = 'Carbon\Carbon';
            }

            return 'datetime';
        }

        return match ($type) {
            ColumnTypeEnum::INT => 'integer',
            ColumnTypeEnum::FLOAT => 'float',
            ColumnTypeEnum::BOOLEAN => 'boolean',
            default => 'string',
        };
    }

    /**
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function getEntityColumns(string $entityName): array
    {
        if (! isset(self::$entityColumns[$entityName])) {
            self::$entityColumns[$entityName] = $this->sm->listTableColumns($entityName);
        }

        return self::$entityColumns[$entityName];
    }

    /**
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function getEntityIndexes(string $entityName): array
    {
        if (! isset(self::$entityIndexes[$entityName])) {
            self::$entityIndexes[$entityName] = $this->sm->listTableIndexes($entityName);
        }

        return self::$entityIndexes[$entityName];
    }

    private function mapColumnType(Type $type): ColumnTypeEnum
    {
        if ($type instanceof SmallIntType ||
            $type instanceof BigIntType ||
            $type instanceof IntegerType
        ) {
            return ColumnTypeEnum::INT;
        }
        if ($type instanceof DateType ||
            $type instanceof DateTimeType ||
            $type instanceof DateImmutableType ||
            $type instanceof DateTimeImmutableType ||
            $type instanceof DateTimeTzType ||
            $type instanceof DateTimeTzImmutableType
        ) {
            return ColumnTypeEnum::DATETIME;
        }
        if ($type instanceof StringType ||
            $type instanceof TextType) {
            return ColumnTypeEnum::STRING;
        }
        if ($type instanceof DecimalType ||
            $type instanceof SmallFloatType ||
            $type instanceof FloatType
        ) {
            return ColumnTypeEnum::FLOAT;
        }
        if ($type instanceof BooleanType) {
            return ColumnTypeEnum::BOOLEAN;
        }

        return ColumnTypeEnum::STRING;
    }
}
