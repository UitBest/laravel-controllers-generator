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
use TimoCuijpers\LaravelControllersGenerator\Entities\PrimaryKey;
use TimoCuijpers\LaravelControllersGenerator\Entities\Property;
use TimoCuijpers\LaravelControllersGenerator\Entities\Relationships\BelongsTo;
use TimoCuijpers\LaravelControllersGenerator\Entities\Relationships\BelongsToMany;
use TimoCuijpers\LaravelControllersGenerator\Entities\Relationships\HasMany;
use TimoCuijpers\LaravelControllersGenerator\Entities\Relationships\MorphMany;
use TimoCuijpers\LaravelControllersGenerator\Entities\Relationships\MorphTo;
use TimoCuijpers\LaravelControllersGenerator\Entities\Table;
use TimoCuijpers\LaravelControllersGenerator\Enums\ColumnTypeEnum;
use TimoCuijpers\LaravelControllersGenerator\Helpers\NamingHelper;
use Illuminate\Support\Str;

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

        $morphables = [];

        foreach ($tables as $table) {
            $fks = $table->getForeignKeys();
            $columns = $this->getEntityColumns($table->getName());
            $indexes = $this->getEntityIndexes($table->getName());
            $properties = [];
            $rules = [];

            $dbTable = new Table($table->getName(), dbEntityNameToModelName($table->getName()));
            if (isset($indexes['primary'])) {
                $primaryKeyName = $indexes['primary']->getColumns()[0];
                foreach ($columns as $column) {
                    if ($column->getName() == $indexes['primary']->getColumns()[0]) {
                        $dbTable->primaryKey = new PrimaryKey($primaryKeyName, $column->getAutoincrement(), $this->laravelColumnType($this->mapColumnType($column->getType())));
                    }
                    break;
                }
            }

            foreach ($indexes as $index) {
                if(!$index->isPrimary() && $index->isUnique()){
                    foreach ($index->getColumns() as $columnName){
                        $rules[$columnName][] = 'unique:'.$dbTable->name;
                    };
                }
            }

            $dbTable->fillable = array_filter(
                array_diff(
                    array_keys($columns),
                    array_merge(
                        ['created_at', 'updated_at', 'deleted_at'],
                        $this->getArrayWithPrimaryKey($dbTable)
                    )
                ),
                static function (string $column): bool {
                    foreach (config('controllers-generator.exclude_columns', []) as $pattern) {
                        if (@preg_match($pattern, '') === false) {
                            $found = $pattern === $column;
                        } else {
                            $found = (bool) preg_match($pattern, $column);
                        }

                        if ($found) {
                            return false;
                        }
                    }

                    return true;
                }
            );
            if (in_array('password', $dbTable->fillable)) {
                $dbTable->hidden = ['password'];
            }

            $dbTable->timestamps = array_key_exists('created_at', $columns) && array_key_exists('updated_at', $columns);
            $dbTable->softDeletes = array_key_exists('deleted_at', $columns);

            /** @var Column $column */
            foreach ($columns as $column) {
                // TODO: Add $rules

                $laravelColumnType = $this->laravelColumnType($this->mapColumnType($column->getType()), $dbTable);
                $dbTable->casts[$column->getName()] = $this->laravelColumnTypeForCast($this->mapColumnType($column->getType()), $dbTable);

                $fieldType = match ($laravelColumnType) {
                    'integer', 'float' => 'numeric',
                    'boolean' => 'boolean',
                    default => 'string',
                };

                $rules[$column->getName()][] = $column->getNotnull() ? 'required' : 'nullable';
                $rules[$column->getName()][] = 'size:' . ($column->getLength() ?? $column->getPrecision());
                $rules[$column->getName()][] = $fieldType;

                if ($fieldType === 'numeric') {
                    $rules[$column->getName()][] = 'max_digits:' . $column->getScale();
                    $rules[$column->getName()][] = 'min_digits:' . ($column->getFixed() ? $column->getScale() : '0');
                }

                $properties[] = new Property(
                    '$' . $column->getName(),
                    ($this->typeColumnPropertyMaps[$laravelColumnType] ?? $laravelColumnType) . ($column->getNotnull() ? '' : '|null'),
                    comment: $column->getComment()
                ); // $laravelColumnType.($column->getNotnull() ? '' : '|null').' $'.$column->getName();

                // Get morph
                if (str_ends_with($column->getName(), '_type') && in_array(str_replace('_type', '', $column->getName()) . '_id', array_keys($columns))) {
                    $dbTable->morphTo[] = new MorphTo(str_replace('_type', '', $column->getName()));

                    $morphables[str_replace('_type', '', $column->getName())] = $dbTable->className;
                }
            }
            $dbTable->rules = $rules;
            $dbTable->properties = $properties;

            foreach ($fks as $fk) {
                if (isRelationshipToBeAdded($dbTable->name, $fk->getForeignTableName())) {
                    $dbTable->addBelongsTo(new BelongsTo($fk));
                }
            }

            $dbTables[$table->getName()] = $dbTable;
        }

        foreach ($dbTables as $dbTable) {
            foreach ($dbTable->belongsTo as $foreignName => $belongsTo) {
                $foreignTableName = $belongsTo->foreignKey->getForeignTableName();
                $foreignKeyName = $belongsTo->foreignKey->getLocalColumns()[0];
                $localKeyName = $belongsTo->foreignKey->getForeignColumns()[0];

                if ($localKeyName == $dbTables[$foreignTableName]->primaryKey) {
                    $localKeyName = null;
                }
                if (isRelationshipToBeAdded($dbTable->name, $foreignTableName)) {
                    $dbTables[$foreignTableName]->addHasMany(new HasMany($dbTable->className, $foreignKeyName, $localKeyName));
                }

                $dbTable->rules[$foreignKeyName][] = 'exists:'.$foreignTableName.','.$localKeyName;

                if (count($dbTable->belongsTo) > 1) {
                    foreach ($dbTable->belongsTo as $subForeignName => $subBelongsTo) {
                        $subForeignTableName = $subBelongsTo->foreignKey->getForeignTableName();

                        if ($foreignTableName != $subForeignTableName) {
                            if (isRelationshipToBeAdded($dbTable->name, $subForeignTableName)) {
                                $tableIndexes = $this->getEntityIndexes($dbTables[$foreignTableName]->name);
                                $relatedTableIndexes = $this->getEntityIndexes($subForeignTableName);
                                $pivotIndexes = $this->getEntityIndexes($dbTable->name);

                                $foreignPivotKey = $tableIndexes['primary']->getColumns()[0];
                                $relatedPivotKey = $relatedTableIndexes['primary']->getColumns()[0];
                                $pivotPrimaryKey = isset($pivotIndexes['primary']) ? $pivotIndexes['primary']->getColumns()[0] : null;

                                $pivotColumns = $this->getEntityColumns($dbTable->name);
                                $pivotTimestamps = array_key_exists('created_at', $pivotColumns) && array_key_exists('updated_at', $pivotColumns);
                                $pivotAttributes = array_diff(
                                    array_keys($pivotColumns),
                                    array_merge(
                                        [$foreignPivotKey, $relatedPivotKey, $pivotPrimaryKey],
                                        $pivotTimestamps ? ['created_at', 'updated_at'] : []
                                    )
                                );

                                $belongsToMany = new BelongsToMany(
                                    $subForeignTableName,
                                    $dbTable->name,
                                    $foreignPivotKey,
                                    $relatedPivotKey,
                                    pivotAttributes: $pivotAttributes
                                );
                                $belongsToMany->timestamps = $pivotTimestamps;

                                $dbTables[$foreignTableName]->addBelongsToMany($belongsToMany);
                            }
                        }
                    }
                }
            }

            // Morph many
            foreach (config('controllers-generator.morphs') as $table => $relationship) {
                if ($table == $dbTable->name) {
                    $dbTable->morphMany[] = new MorphMany(
                        NamingHelper::caseRelationName(Str::plural($morphables[$relationship])),
                        $morphables[$relationship],
                        $relationship,
                    );
                }
            }
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

    /**
     * @return list<string>
     */
    private function getArrayWithPrimaryKey(Table $dbTable): array
    {
        return $dbTable->primaryKey !== null ? (config('controllers-generator.primary_key_in_fillable', false) && ! empty($dbTable->primaryKey->name) ? [] : [$dbTable->primaryKey->name]) : [];
    }
}
