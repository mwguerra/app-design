<?php

namespace MWGuerra\AppDesign\Generators;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class RequestGenerator extends Generator
{
    protected $modelName;
    protected $tableName;
    protected $destinationBasePath;
    protected $stubsBasePath;
    protected $columns;

    public function __construct(array $resource, bool $dryRun = false)
    {
        parent::__construct($dryRun);

        $this->modelName = Str::studly($resource['name']);
        $this->tableName = $resource['database']['tableName'] ?? Str::snake(Str::plural($resource['name']));
        $this->destinationBasePath = config('appdesign.paths.requests');
        $this->stubsBasePath = $this->packageBasePath . config('appdesign.paths.stubs.requests');
        $this->columns = $resource['database']['columns'] ?? [];
    }

    public function generate(): array
    {
        $requestTypes = ['Store', 'Update'];

        foreach ($requestTypes as $type) {
            $requestPath = $this->generateFormRequest($type);
        }

        return [
            'log' => $this->getLog(),
            'files' => $this->getFiles()
        ];
    }

    protected function generateFormRequest(string $type): string
    {
        $stubPath = $this->stubsBasePath . "/FormRequest.php.stub";
        $requestPath = $this->destinationBasePath . "/{$this->modelName}{$type}Request.php";
        $validationRules = $this->generateValidationRules($type);

        $replacements = [
            '##model_name##' => $this->modelName,
            '##model_singular##' => lcfirst(Str::singular($this->modelName)),
            '##model_plural##' => lcfirst(Str::plural($this->modelName)),
            '##request_type##' => $type,
            '##rules##' => $validationRules
        ];

        if ($this->dryRun) {
            $this->log("Dry run: Would generate {$type} request at {$requestPath}");
            return $requestPath;
        }

        $this->replaceInStubAndSave($stubPath, $requestPath, $replacements);
        $this->log("Generated {$type} request: {$requestPath}");
        return $requestPath;
    }

    protected function generateValidationRules(string $type): string
    {
        $rules = [];
        foreach ($this->columns as $column) {
            $columnName = $column['name'];
            $rule = $this->determineValidationRule($column, $type);
            if ($rule) {
                $rules[] = "'$columnName' => '$rule'";
            }
        }
        return implode(",\n            ", $rules);
    }

    protected function determineValidationRule($column, $type): string
    {
        $rules = $this->buildRuleSet($column, $type);
        return implode('|', $rules);
    }

    private function buildRuleSet($column, $type): array
    {
        $rules = $this->commonRules($column, $type);
        $this->typeSpecificRules($column, $rules);
        if (!empty($column['nullable'])) {
            $rules[] = 'nullable';
        }
        return $rules;
    }

    private function commonRules($column, $type): array
    {
        $rules = [];
        if ($type === 'Store' && !empty($column['requiredOnCreate'])) {
            $rules[] = 'required';
        } elseif ($type === 'Update') {
            $rules[] = !empty($column['requiredOnUpdate']) ? 'sometimes|required' : 'sometimes';
        }
        return $rules;
    }

    private function typeSpecificRules($column, &$rules)
    {
        switch ($column['type']) {
            case 'string':
            case 'text':
                $this->stringRules($column, $rules);
                break;
            case 'date':
                $rules[] = 'date';
                break;
            case 'datetime':
                $rules[] = 'date_format:Y-m-d H:i:s';
                break;
            case 'boolean':
                $rules[] = 'boolean';
                break;
            case 'number':
            case 'integer':
                $this->numericRules($column, $rules);
                break;
            case 'enum':
                $rules[] = 'in:' . implode(',', $column['allowed']);
                break;
        }
    }

    private function stringRules($column, &$rules)
    {
        $rules[] = 'string';
        if (isset($column['min'])) {
            $rules[] = "min:{$column['min']}";
        }
        if (isset($column['max'])) {
            $rules[] = "max:{$column['max']}";
        }
        if (!empty($column['unique'])) {
            $rules[] = "unique:{$this->tableName},{$column['name']}";
        }
    }

    private function numericRules($column, &$rules)
    {
        $rules[] = $column['type'] === 'integer' ? 'integer' : 'numeric';
        if (isset($column['min'])) {
            $rules[] = "min:{$column['min']}";
        }
        if (isset($column['max'])) {
            $rules[] = "max:{$column['max']}";
        }
    }
}
