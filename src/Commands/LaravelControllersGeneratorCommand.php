<?php

declare(strict_types=1);

namespace TimoCuijpers\LaravelControllersGenerator\Commands;

use Doctrine\DBAL\Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Filesystem\Filesystem;
use TimoCuijpers\LaravelControllersGenerator\Drivers\DriverFacade;
use TimoCuijpers\LaravelControllersGenerator\Entities\Entity;
use TimoCuijpers\LaravelControllersGenerator\Exceptions\DatabaseDriverNotFound;
use TimoCuijpers\LaravelControllersGenerator\Writers\WriterInterface;

class LaravelControllersGeneratorCommand extends Command
{
    public $signature = 'laravel-controllers-generator:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate controllers from generated models';

    private ?string $singleEntityToCreate = null;

    /**
     * Execute the console command.
     *
     * @throws Exception
     * @throws DatabaseDriverNotFound
     * @throws \Exception
     */
    public function handle(): int
    {
        $connection = $this->getConnection();
        $schema = $this->getSchema($connection);
        $this->singleEntityToCreate = $this->getTable();

        $connector = DriverFacade::instance(
            (string) config('database.connections.'.config('database.default').'.driver'),
            $connection,
            $schema,
            $this->singleEntityToCreate
        );

        $dbTables = $connector->listTables();

        $fileSystem = new Filesystem;

        $path = $this->sanitizeBaseClassesPath(config('controllers-generator.path', app_path('Http/Controllers')));

        if (config('controllers-generator.clean_controllers_directory_before_generation', true)) {
            $fileSystem->cleanDirectory($path);
        }

        foreach ($dbTables as $name => $dbEntity) {
            if ($this->entityToGenerate($name)) {
                $createBaseClass = config('controllers-generator.base_files.enabled', false);
                if ($createBaseClass) {
                    $baseClassesPath = $path.DIRECTORY_SEPARATOR.'Base';
                    $this->createBaseClassesFolder($baseClassesPath);
                    $dbEntity->namespace = config('controllers-generator.namespace', 'App\Http\Controllers').'\\Base';
                    $fileName = $dbEntity->className.'.php';
                    $fileSystem->put($baseClassesPath.DIRECTORY_SEPARATOR.$fileName, $this->modelContent($dbEntity->className, $dbEntity));

                    $dbEntity->cleanForBase();
                }

                $fileName = $dbEntity->className.'.php';
                $fileSystem->makeDirectory($path, 0755, true, true);
                $fileSystem->put($path.DIRECTORY_SEPARATOR.$fileName, $this->modelContent($dbEntity->className, $dbEntity));
            }
        }

        $this->info($this->singleEntityToCreate === null ? 'Check out your controllers' : "Check out your {$this->singleEntityToCreate} controller");

        return self::SUCCESS;
    }

    protected function getStub(): string
    {
        return $this->resolveStubPath();
    }

    /**
     * /**
     *  Resolve the fully-qualified path to the stub.
     */
    private function resolveStubPath(): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim('/src/Entities/stubs/model.stub', '/')))
            ? $customPath
            : __DIR__.'/../Entities/stubs/model.stub';
    }

    /**
     * @throws \Exception
     */
    private function modelContent(string $className, Entity $dbEntity): string
    {
        $content = file_get_contents($this->getStub());
        if ($content !== false) {
            $arImports = [];

            if ($dbEntity->importLaravelModel()) {
                $arImports[] = config('controllers-generator.parent', 'App\Http\Controllers\Controller');
            }

            if ($dbEntity->softDeletes) {
                $arImports[] = SoftDeletes::class;
            }

            $dbEntity->imports = array_merge($dbEntity->imports, $arImports);

            $versionedWriter = 'TimoCuijpers\LaravelControllersGenerator\Writers\Laravel'.$this->resolveLaravelVersion().'\\Writer';
            /** @var WriterInterface $writer */
            $writer = new $versionedWriter($className, $dbEntity, $content);

            return $writer->writeModelFile();
        }

        throw new \Exception('Error reading stub file');
    }

    private function getConnection(): string
    {
        return config('database.default');
    }

    private function getSchema(string $connection): string
    {
        return config('database.connections.'.$connection.'.database');
    }

    private function getTable(): ?string
    {
        return null;
    }

    private function entityToGenerate(string $entity): bool
    {
        return ! in_array($entity, config('controllers-generator.except', [])) && $this->singleEntityToCreate === null || ($this->singleEntityToCreate && $this->singleEntityToCreate === $entity);
    }

    private function sanitizeBaseClassesPath(string $path): string
    {
        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    private function createBaseClassesFolder(string $path): void
    {
        if (! file_exists($path)) {
            mkdir($path, 0755, true);
        }
        /*if (! file_exists(base_path($path))) {
            mkdir(base_path($path), 0755, true);
        }*/
    }

    private function resolveLaravelVersion(): int
    {
        return (int) strstr(app()->version(), '.', true);
    }
}
