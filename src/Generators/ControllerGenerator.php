<?php

namespace MWGuerra\AppDesign\Generators;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class ControllerGenerator extends Generator
{
    protected string $modelName;
    protected string $destinationBasePath;
    protected string $stubsBasePath;

    public function __construct(array $resource)
    {
        parent::__construct();

        $this->modelName = Str::studly($resource['name']);
        $this->destinationBasePath = config('appdesign.paths.controllers');
        $this->stubsBasePath = $this->packageBasePath . config('appdesign.paths.stubs.controllers');
    }

    /**
     * @return array
     */
    public function generate(): array
    {
        $stubPath = $this->stubsBasePath . '/RestController.php.stub';
        $controllerPath = $this->destinationBasePath . "{$this->modelName}Controller.php";

        $this->replaceInStubAndSave($stubPath, $controllerPath, [
            '##model_name##' => $this->modelName,
            '##model_singular##' => lcfirst(Str::singular($this->modelName)),
            '##model_plural##' => lcfirst(Str::plural($this->modelName)),
        ]);

        $this->copyServiceClass();

        return [
            'log' => $this->getLog(),
            'files' => $this->getFiles(),
        ];
    }

    /**
     * @return string|null
     */
    protected function copyServiceClass(): ?string
    {
        $stubPath = $this->packageBasePath . config('appdesign.paths.stubs.services') . '/ModelService.php';
        $servicePath = app_path('Services/ModelService.php');

        $this->copyFile($stubPath, $servicePath);

        return $servicePath;
    }

}
