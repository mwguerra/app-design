<?php

namespace MWGuerra\AppDesign\Generators;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class FactoryGenerator extends Generator
{
    protected $resource;
    protected $stubPath;

    public function __construct(array $resource)
    {
        parent::__construct();

        $this->resource = $resource;
        $this->stubPath = $this->packageBasePath . config('appdesign.paths.stubs.factories');
    }

    public function generate(): array
    {
        $className = Str::studly($this->resource['name']);
        $factoryClassName = $className . 'Factory';
        $definition = $this->generateDefinition($this->resource['database']['columns'] ?? []);

        $factoryFilePath = database_path('factories/' . $factoryClassName . '.php');

        $this->replaceInStubAndSave($this->stubPath . '/ModelFactory.php.stub', $factoryFilePath, [
            '{{factoryClassName}}' => $factoryClassName,
            '{{modelName}}' => $className,
            '{{definition}}' => $definition,
            '{{relationships}}' => $this->generateUseStatementsForModels(),
        ]);

        return [
            'log' => $this->getLog(),
            'files' => $this->getFiles(),
        ];
    }

    protected function generateDefinition(array $columns): string
    {
        $definitionBody = collect($columns)->reject(function ($column) {
            return in_array($column['name'], ['id', 'created_at', 'updated_at']);
        })->map(function ($column) {
            if (!empty($column['foreignKey'])) {
                // Assume the column name is like 'user_id', 'post_id' etc.
                $relatedTableName = explode('_', $column['name'])[0]; // 'user', 'post'
                $relatedModelName = Str::studly($relatedTableName); // User, Post
                return "'{$column['name']}' => {$relatedModelName}::factory()";
            } else {
                $fakerMethod = $this->mapColumnToFaker($column);
                return "'{$column['name']}' => fake()->{$fakerMethod}";
            }
        })->implode(",\n\t\t\t");

        return "\t\t\t$definitionBody";
    }

    protected function mapColumnToFaker($column): string
    {
        switch ($column['type']) {
            case 'text':
                return 'text';
            case 'integer':
                return 'numberBetween(1, 100)';
            case 'boolean':
                return 'boolean';
            default:
                return 'word';
        }
    }

    protected function extractModelNames(): array
    {
        $columns = $this->resource['database']['columns']?? [];
        $relationships = $this->resource['database']['relationships']?? [];

        $modelNamesFromColumns = collect($columns)
            ->where('foreignKey', true)
            ->map(function ($column) {
                return Str::studly(explode('_', $column['name'])[0]);
            });

        $modelNamesFromRelationships = collect($relationships)
            ->pluck('model')
            ->map(function ($modelName) {
                return Str::studly($modelName);
            });

        return $modelNamesFromColumns
            ->merge($modelNamesFromRelationships)
            ->unique()
            ->values()
            ->toArray();
    }

    protected function generateUseStatementsForModels(): string
    {
        $modelNames = $this->extractModelNames();

        return "\n" . collect($modelNames)->map(function ($modelName) {
            return "use App\\Models\\{$modelName};";
        })->implode("\n");
    }
}
