<?php

namespace MWGuerra\AppDesign\Generators;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class PolicyGenerator extends Generator
{
    protected string $modelName;
    protected string $destinationBasePath;
    protected string $stubsBasePath;

    public function __construct(array $resource, bool $dryRun = false)
    {
        parent::__construct($dryRun);
        $this->modelName = Str::studly($resource['name']);
        $this->destinationBasePath = config('appdesign.paths.policies');
        $this->stubsBasePath = $this->packageBasePath . config('appdesign.paths.stubs.policies');
    }

    /**
     * Generates a policy file for the specified model.
     *
     * @return array An array containing logs and paths to the potentially created files.
     */
    public function generate(): array
    {
        $stubPath = $this->stubsBasePath . '/Policy.php.stub';
        $policyPath = $this->destinationBasePath . "{$this->modelName}Policy.php";

        try {
            $replacements = [
                '##model_name##' => $this->modelName,
                '##model_singular##' => $this->modelName === 'User' ? 'target' : lcfirst(Str::singular($this->modelName)),
                '##model_plural##' => lcfirst(Str::plural($this->modelName)),
            ];

            if ($this->dryRun) {
                $this->log("Dry run: Would generate policy at $policyPath");
            } else {
                $this->replaceInStubAndSave($stubPath, $policyPath, $replacements);
                $this->log("Generated policy at $policyPath");
            }
        } catch (\Exception $e) {
            $this->log("Error generating policy: " . $e->getMessage());
        }

        return [
            'log' => $this->getLog(),
            'files' => $this->getFiles(),
        ];
    }
}
