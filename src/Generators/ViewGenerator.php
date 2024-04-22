<?php

namespace MWGuerra\AppDesign\Generators;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class ViewGenerator extends Generator
{
    protected $resource;
    protected $destinationBasePath;
    protected $stubsBasePath;

    public function __construct(array $resource)
    {
        parent::__construct();

        $this->resource = $resource;
        $this->destinationBasePath = config('appdesign.paths.views');
        $this->stubsBasePath = $this->packageBasePath . config('appdesign.paths.stubs.views');
    }

    public function generate(): array
    {
        $modelName = Str::studly($this->resource['name']);
        $viewDirectory = "{$this->destinationBasePath}{$modelName}";

        // Ensure the directory exists
        $this->filesystem->ensureDirectoryExists($viewDirectory);

        // List of view files to generate
        $views = ['Index', 'Create', 'Edit', 'Show'];

        foreach ($views as $viewName) {
            $this->generateViewFile($modelName, $viewName, $viewName, $viewDirectory);
            $this->generateViewFile($modelName, $viewName, $viewName . 'Content', $viewDirectory);
        }

        $this->publishComponents();
        $this->generateStorybookFile($viewDirectory);

        return [
            'log' => $this->getLog(),
            'files' => $this->getFiles()
        ];
    }

    protected function publishComponents(): void
    {
        $componentsStubPath = $this->stubsBasePath . '/Components';

        // Get all Vue component stubs in the directory
        $componentFiles = $this->filesystem->files($componentsStubPath);

        foreach ($componentFiles as $file) {
            $destPath = config('appdesign.paths.components') . $file->getFilename();
            $this->copyFile($file->getPathname(), $destPath);
        }
    }

    protected function generateViewFile($modelName, $viewName, $viewFilename, $viewDirectory): string
    {
        $stubPath = $this->stubsBasePath . "/Pages/{$viewFilename}.vue.stub";
        $filePath = "{$viewDirectory}/{$viewFilename}.vue";

        // Additional logic to handle fields based on the view type
        $fields = $this->getFieldsForView($modelName, $viewName);
        $fieldComponents = $this->generateFieldComponents($fields, $viewName);

        $this->replaceInStubAndSave($stubPath, $filePath, [
            '##page_title##' => "{$viewName} {$modelName}",
            '##model_name##' => $modelName,
            '##model_singular##' => lcfirst(Str::singular($modelName)),
            '##model_plural##' => lcfirst(Str::plural($modelName)),
            '##fields##' => $fieldComponents,
        ]);

            // var_dump([
            //     '##page_title##' => "{$viewName} {$modelName}",
            //     '##model_name##' => $modelName,
            //     '##model_singular##' => lcfirst(Str::singular($modelName)),
            //     '##model_plural##' => lcfirst(Str::plural($modelName)),
            //     '##fields##' => $fieldComponents,
            // ], $filePath);

        return $filePath;
    }

    protected function getFieldsForView($modelName, $viewName): array
    {
        $fields = [];

        foreach ($this->resource['database']['columns'] as $column) {
            // For the Create view, include only required fields that are not timestamps.
            // For the Edit view, include all fields except timestamps.
            // Show view will include all fields but as readonly.
            if (($viewName == 'Create' && isset($column['validation']) && !in_array('nullable', $column['validation'])) ||
                $viewName == 'Edit' ||
                $viewName == 'Show') {
                if (!in_array($column['name'], ['created_at', 'updated_at'])) {
                    $fields[] = $column;
                }
            }
        }

        return $fields;
    }

    protected function getHtmlInputType($field): string
    {
        $htmlInputTypes = [
            'textarea' => ['text'],
            'text' => ['string'],
            'checkbox' => ['boolean', 'tinyint'],
            'number' => ['bigInteger', 'integer', 'decimal'],
            'datetime-local' => ['dateTime', 'date'],
        ];

        if (isset($field['foreignKey']) && $field['foreignKey']) {
            return 'select';
        }

        foreach ($htmlInputTypes as $htmlType => $dbTypes) {
            if (in_array($field['type'], $dbTypes)) {
                return $htmlType;
            }
        }

        // Default to text if no match is found
        return 'text';
    }

    protected function generateFieldComponents($fields, $viewName): string
    {
        $componentLines = []; // Initialize an array to hold lines/components.

        foreach ($fields as $field) {
            $readonly = $viewName === 'Show' ? 'true' : 'false';

            $type = $this->getHtmlInputType($field);

            $label = ucfirst($field['name']);
            $name = $field['name'];

            if ($type === 'select' && str_ends_with($name, '_id')) {
                // Assuming dropdowns for relationships (fields ending in '_id')
                $modelName = rtrim($name, '_id');
                $optionsVarName = $modelName . 'Options'; // Example: userId becomes userIdOptions

                $componentLines[] = "<BaseInput type=\"select\" name=\"{$name}\" label=\"{$label}\" :options=\"{$optionsVarName}\" readonly=\"{$readonly}\" />";
            } else {
                $componentLines[] = "<BaseInput type=\"{$type}\" name=\"{$name}\" label=\"{$label}\" readonly=\"{$readonly}\" />";
            }
        }

        return implode("\n", $componentLines);
    }

    protected function generateStorybookFile($viewDirectory): string
    {
        $modelName = Str::studly($this->resource['name']);

        $storiesStubPath = "{$this->stubsBasePath}/Pages/model.stories.js.stub";
        $destPath = "{$viewDirectory}/{$modelName}.stories.js";

        $this->replaceInStubAndSave($storiesStubPath, $destPath, [
            '##title##' => "Pages/{$modelName}",
        ]);

        return $destPath;
    }
}
