<?php

declare(strict_types=1);

namespace TimoCuijpers\LaravelControllersGenerator\Drivers\SQLite;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Column;
use Illuminate\Support\Facades\DB;
use TimoCuijpers\LaravelControllersGenerator\Concerns\DBALable;
use TimoCuijpers\LaravelControllersGenerator\Contracts\DriverConnectorInterface;
use TimoCuijpers\LaravelControllersGenerator\Drivers\DriverConnector;
use TimoCuijpers\LaravelControllersGenerator\Entities\Property;
use TimoCuijpers\LaravelControllersGenerator\Entities\View;

class Connector extends DriverConnector implements DriverConnectorInterface
{
    use DBALable;

    /**
     * @throws Exception
     */
    public function __construct(?string $connection = null, ?string $schema = null, ?string $table = null)
    {
        parent::__construct($connection, $schema, $table);

        $this->conn = DriverManager::getConnection($this->connectionParams());
        $platform = $this->conn->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping('enum', 'string');
        $this->sm = $this->conn->createSchemaManager();
    }

    public function connectionParams(): array
    {
        /** @phpstan-ignore-next-line */
        return [
            'driver' => 'pdo_'.config('database.connections.'.config('database.default').'.driver'),
            'path' => (string) config('database.connections.'.config('database.default').'.database'),
        ];
    }

    private function getView(string $viewName): View
    {
        $columns = $this->getEntityColumns($viewName);
        $properties = [];

        $dbView = new View($viewName, dbEntityNameToModelName($viewName));
        $dbView->fillable = array_diff(
            array_keys($columns),
            ['created_at', 'updated_at', 'deleted_at']
        );

        /** @var Column $column */
        foreach ($columns as $column) {
            $laravelColumnType = $this->laravelColumnType($this->mapColumnType($column->getType()), $dbView);
            $dbView->casts[$column->getName()] = $this->laravelColumnTypeForCast($this->mapColumnType($column->getType()), $dbView);

            $properties[] = new Property(
                '$'.$column->getName(),
                ($this->typeColumnPropertyMaps[$laravelColumnType] ?? $laravelColumnType).($column->getNotnull() ? '' : '|null'),
                true
            );
        }
        $dbView->properties = $properties;

        return $dbView;
    }

    public function listViews(): array
    {
        /** @var array<string, View> $dbViews */
        $dbViews = [];

        $sql = "SELECT name FROM sqlite_master WHERE type = 'view'";
        $rows = DB::select($sql);

        foreach ($rows as $row) {
            $dbViews[$row->name] = $this->getView($row->name);
        }

        return $dbViews;
    }
}
