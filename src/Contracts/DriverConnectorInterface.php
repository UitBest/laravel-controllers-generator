<?php

declare(strict_types=1);

namespace TimoCuijpers\LaravelControllersGenerator\Contracts;

use TimoCuijpers\LaravelControllersGenerator\Entities\Entity;
use TimoCuijpers\LaravelControllersGenerator\Entities\Table;
use TimoCuijpers\LaravelControllersGenerator\Entities\View;
use TimoCuijpers\LaravelControllersGenerator\Enums\ColumnTypeEnum;

interface DriverConnectorInterface
{
    /**
     * @return array{
     *     'driver': 'ibm_db2'|'mysqli'|'oci8'|'pdo_mysql'|'pdo_oci'|'pdo_pgsql'|'pdo_sqlite'|'pdo_sqlsrv'|'pgsql'|'sqlite3'|'sqlsrv'
     * }
     */
    public function connectionParams(): array;

    /**
     * @return array<string, Table>
     */
    public function listTables(): array;

    /**
     * @return array<string, View>
     */
    public function listViews(): array;

    /**
     * @return array<string, mixed>
     */
    public function getEntityColumns(string $entityName): array;

    /**
     * @return array<string, mixed>
     */
    public function getEntityIndexes(string $entityName): array;

    public function laravelColumnTypeForCast(ColumnTypeEnum $type, ?Entity $dbTable = null): string;

    public function laravelColumnType(ColumnTypeEnum $type, ?Entity $dbTable = null): string;
}
