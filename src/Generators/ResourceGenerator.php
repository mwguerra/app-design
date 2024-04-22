<?php

namespace MWGuerra\AppDesign\Generators;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class ResourceGenerator extends Generator
{
    protected $modelName;
    protected $destinationBasePath;
    protected $stubsBasePath;

    public function __construct(array $resource, bool $dryRun = false)
    {
        parent::__construct($dryRun);

        $this->modelName = Str::studly($resource['name']);
        $this->destinationBasePath = config('appdesign.paths.resources');
        $this->stubsBasePath = $this->packageBasePath . config('appdesign.paths.stubs.resources');
    }

    public function generate(): array
    {
        $this->generateFile('Resource.php.stub', "{$this->modelName}Resource.php");
        $this->generateFile('ResourceCollection.php.stub', "{$this->modelName}Collection.php");

        return [
            'log' => $this->getLog(),
            'files' => $this->getFiles()
        ];
    }

    protected function generateFile($stubName, $fileName): string
    {
        $stubPath = $this->stubsBasePath . "/$stubName";
        $destinationPath = $this->destinationBasePath . "/$fileName";

        $replacements = [
            '##model_name##' => $this->modelName,
            '##model_singular##' => lcfirst(Str::singular($this->modelName)),
            '##model_plural##' => lcfirst(Str::plural($this->modelName)),
        ];

        if ($this->dryRun) {
            $this->log("Dry run: Would generate file from stub {$stubName} at {$destinationPath}");
            return $destinationPath;
        }

        $this->replaceInStubAndSave($stubPath, $destinationPath, $replacements);
        $this->log("Generated file {$fileName} from stub {$stubName}");

        return $destinationPath;
    }
}
