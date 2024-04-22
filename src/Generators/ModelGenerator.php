<?php

namespace MWGuerra\AppDesign\Generators;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class ModelGenerator extends Generator
{
    protected $resource;
    protected $stubPath;
    protected $relationshipStubPaths;

    public function __construct(array $resource)
    {
        parent::__construct();

        $this->resource = $resource;
        $this->stubPath = $this->packageBasePath . config('appdesign.paths.stubs.models.core');

        $packageBasePath = $this->packageBasePath;
        $this->relationshipStubPaths = collect(config('appdesign.paths.stubs.models.relationships'))
            ->map(function ($item) use ($packageBasePath) {
                return $packageBasePath . $item;
            })
            ->toArray();
    }

    public function generate(): array
    {
        $className = Str::studly($this->resource['name']);
        $modelFilePath = app_path('Models/' . $className . '.php');

        $this->replaceInStubAndSave($this->stubPath, $modelFilePath, [
            '{{className}}' => $className,
            '{{useTraits}}' => $this->generateUseTraits(),
            '{{fillable}}' => $this->generateAttributeList('fillable'),
            '{{hidden}}' => $this->generateAttributeList('hidden'),
            '{{relationships}}' => $this->generateRelationshipMethods(),
            '{{useAdditionalTraits}}' => '',
            '{{casts}}' => ''
        ]);

        return [
            'log' => $this->getLog(),
            'files' => $this->getFiles(),
        ];
    }

    protected function generateAttributeList($type): string
    {
        $attributeList = collect($this->resource['database']['columns'])
            ->reject(function ($column) use ($type) {
                $hidden = $column['hidden'] ?? false;

                return ($type === 'fillable' && (!empty($column['foreignKey']) || $hidden)) ||
                       ($type === 'hidden' && !$hidden);
            })
            ->map(function ($column) {
                return "'{$column['name']}'";
            })->implode(",\n\t\t");

        $variableDeclaration = "\n\tprotected \$$type = [\n\t\t$attributeList\n\t];\n\n";

        return !empty($attributeList) ? $variableDeclaration : '';
    }

    protected function generateRelationshipMethods(): string
    {
        return collect($this->resource['database']['relationships'] ?? [])
            ->map(function ($relationship) {
                return $this->generateRelationshipMethod($relationship);
            })->implode("\n");
    }

    /**
     * @throws FileNotFoundException
     */
    protected function generateRelationshipMethod($relationship): string
    {
        $stubPath = $this->relationshipStubPaths[$relationship['type']] ?? null;
        if (!$stubPath) {
            throw new FileNotFoundException("Stub for relationship type '{$relationship['type']}' not found.");
        }

        $stub = $this->readFromFile($stubPath);

        $methodName = Str::camel($relationship['model']) . (Str::endsWith($relationship['type'], 'Many') ? 's' : '');

        return $this->replaceInStub($stub, [
            '{{relationshipMethodName}}' => $methodName,
            '{{RelatedModel}}' => $relationship['model'],
            '{{foreignKeyParameter}}' => isset($relationship['foreignKey']) ? ", '{$relationship['foreignKey']}'" : '',
            '{{localKeyParameter}}' => $relationship['localKey'] ?? '',
            '{{ownerKeyParameter}}' => '',
            '{{tableParameter}}' => '',
            '{{foreignPivotKeyParameter}}' => '',
            '{{relatedPivotKeyParameter}}' => '',
            '{{parentKeyParameter}}' => '',
            '{{relatedKeyParameter}}' => '',
            '{{firstKeyParameter}}' => '',
            '{{secondKeyParameter}}' => '',
            '{{secondLocalKeyParameter}}' => '',
            '{{nameParameter}}' => isset($relationship['as']) ? ", '{$relationship['as']}'" : '',
            '{{typeParameter}}' => '',
            '{{idParameter}}' => '',
        ]);
    }

    protected function generateUseTraits(): string
    {
        return collect($this->resource['traits'] ?? [])
            ->map(fn($trait) => "use $trait;")
            ->implode("\n");
    }
}


