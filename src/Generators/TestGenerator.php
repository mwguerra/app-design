<?php

namespace MWGuerra\AppDesign\Generators;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;

class TestGenerator extends Generator
{
    protected array $model;

    public function __construct(array $model, bool $dryRun = false)
    {
        parent::__construct($dryRun);
        $this->model = $model;
    }

    /**
     * @throws FileNotFoundException
     */
    public function generate(array $testType = ['crud', 'relationships']): array
    {
        $updatedContent = '';

        if (in_array('crud', $testType)) {
            $updatedContent .= $this->generateCrudTests();
        }
        if (in_array('relationships', $testType)) {
            $updatedContent .= $this->generateRelationshipTests();
        }

        return [
            'log' => $this->getLog(),
            'files' => $this->getFiles(),
            'updatedContent' => $updatedContent
        ];
    }

    /**
     * @throws FileNotFoundException
     */
    protected function generateCrudTests(): string
    {
        $modelName = Str::studly($this->model['name']);
        $modelNameLower = Str::camel($this->model['name']);
        $testClassName = "{$modelName}Test";

        $stubPath = $this->packageBasePath . 'Stubs/Tests/Feature/test.php.stub';
        $testFilePath = base_path("tests/Feature/{$testClassName}.php");

        // Initial placeholders for the model test file.
        $replacements = [
            '{{modelName}}' => $modelName,
            '{{modelNameLower}}' => $modelNameLower,
            '{{testClassName}}' => $testClassName,
        ];

        return $this->replaceInStubAndSave($stubPath, $testFilePath, $replacements);
    }

    /**
     * @throws FileNotFoundException
     */
    protected function generateRelationshipTests(): string
    {
        $modelName = Str::studly($this->model['name']);
        $modelNameLower = Str::camel($this->model['name']);
        $testClassName = "{$modelName}Test";
        $testFilePath = base_path("tests/Feature/{$testClassName}.php");

        $relationships = $this->model['relationships'] ?? [];

        $updatedContent = '';

        foreach ($relationships as $relationship) {
            $relationshipType = Str::camel($relationship['type']);
            $relatedModelName = Str::studly($relationship['model']);
            $relatedModelNamePlural = Str::plural($relatedModelName);
            $relatedModelNameLower = Str::camel($relatedModelName);
            $relatedModelNamePluralLower = Str::plural($relatedModelNameLower);
            $relationshipFunctionName = Str::camel($relationship['functionName'] ?? $relatedModelName); // Using specified function name or defaulting to related model name

            $testMethodName = "test{$modelName}Has{$relationshipType}{$relatedModelName}";

            $stubFileName = "{$relationshipType}Test.php.stub";
            $stubPath = $this->packageBasePath . "Stubs/Tests/Feature/Relationships/{$stubFileName}";
            $updatedContent .= $this->replaceInStubAndSave($stubPath, $testFilePath, [
                '{{modelName}}' => $modelName,
                '{{modelNameLower}}' => $modelNameLower,
                '{{relatedModelName}}' => $relatedModelName,
                '{{relatedModelNameLower}}' => $relatedModelNameLower,
                '{{relatedModelNamePlural}}' => $relatedModelNamePlural,
                '{{relatedModelNamePluralLower}}' => $relatedModelNamePluralLower,
                '{{relationshipFunctionName}}' => $relationshipFunctionName,
                '{{testMethodName}}' => $testMethodName,
            ], true);
        }

        return $updatedContent;
    }
}