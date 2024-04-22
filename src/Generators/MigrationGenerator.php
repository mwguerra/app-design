<?php

namespace MWGuerra\AppDesign\Generators;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MigrationGenerator extends Generator
{
    protected array $resource;
    protected string $stubPath;
    protected string $className;
    protected string $tableName;

    public function __construct(array $resource)
    {
        parent::__construct();
        $this->initializeProperties($resource);
    }

    private function initializeProperties($resource): void
    {
        $this->resource = $resource;
        $this->stubPath = $this->packageBasePath . config('appdesign.paths.stubs.migrations');
        $this->className = 'Create' . Str::plural(Str::studly($resource['name'])) . 'Table';
        $this->tableName = $resource['database']['tableName'] ?? Str::snake(Str::plural($resource['name']));
    }

    public function generate(): array
    {
        $this->deleteExistingMigrations();
        $columns = $this->generateColumnsDefinition();
        $migrationFileName = $this->createMigrationFileName();
        $this->saveMigrationFile($migrationFileName, $columns);

        return [
            'log' => $this->getLog(),
            'files' => $this->getFiles(),
            'tables' => [$this->tableName => $this->compileColumnsInfo()]
        ];
    }

    private function createMigrationFileName(): string
    {
        return date('Y_m_d_His') . '_create_' . $this->tableName . '_table.php';
    }

    private function saveMigrationFile($fileName, $columnsDefinition): void
    {
        $migrationFilePath = database_path('migrations/' . $fileName);
        $this->replaceInStubAndSave($this->stubPath, $migrationFilePath, [
            '{{className}}' => $this->className,
            '{{tableName}}' => $this->tableName,
            '{{columns}}' => $columnsDefinition
        ]);
    }

    public function deleteExistingMigrations(): void
    {
        $pattern = '/\d{4}_\d{2}_\d{2}_\d{6}_create_' . $this->tableName . '_table\.php$/';
        $this->deleteFilesMatchingPattern(database_path('migrations'), $pattern);
    }

    protected function compileColumnsInfo(): array
    {
        return collect($this->resource['database']['columns'] ?? [])->mapWithKeys(function ($column) {
            return [$column['name'] => $this->formatColumnInfo($column)];
        })->toArray();
    }

    private function formatColumnInfo($column): array
    {
        return array_merge($column, [
            'nullable' => $column['nullable'] ?? false,
            'unsigned' => $column['unsigned'] ?? false,
            'default' => $column['default'] ?? null
        ]);
    }

    protected function generateColumnsDefinition(): string
    {
        return collect($this->resource['database']['columns'] ?? [])
            ->reject(fn($column) => in_array($column['name'], ['id', 'created_at', 'updated_at']))
            ->map(fn($column) => $this->columnDefinition($column))
            ->implode("\n            ");
    }

    private function columnDefinition($column): string
    {
        if ($column['foreignKey'] ?? false) {
            return $this->handleForeignKeyDefinition($column);
        }
        return $this->handleGenericColumnDefinition($column);
    }

    private function handleForeignKeyDefinition($column): string
    {
        $definition = "\$table->foreignId('{$column['name']}')";
        return $definition . $this->foreignKeyAttributes($column) . ';';
    }

    private function handleGenericColumnDefinition($column): string
    {
        $type = $column['type'];
        $definition = "\$table->$type('{$column['name']}')";
        if ($type === 'decimal') {
            $definition .= ", {$column['precision']}, {$column['scale']}";
        }
        return $definition . $this->columnAttributes($column) . ';';
    }

    private function columnAttributes($column): string
    {
        $attributes = [];

        if ($column['nullable'] ?? false) {
            $attributes[] = '->nullable()';
        }
        if ($column['unsigned'] ?? false) {
            $attributes[] = '->unsigned()';
        }
        if (array_key_exists('default', $column)) {
            $default = var_export($column['default'], true);
            $attributes[] = "->default({$default})";
        }
        if ($column['index'] ?? false) {
            $attributes[] = '->index()';
        }

        return implode('', $attributes);
    }

    private function foreignKeyAttributes($column): string
    {
        $attributes = [];
        if (
            $column['nullable'] ?? false
            || in_array($column['type'], ['nullableUlidMorphs', 'nullableUuidMorphs'])
        ) {
            $attributes[] = "->nullable()";
        }
        if (in_array($column['type'], ['uuid', 'ulid', 'nullableUlidMorphs', 'nullableUuidMorphs'])) {
            $attributes[] = "->index()";
        }

        // Determine the default names based on Laravel's convention
        $defaultTableName = Str::plural(Str::snake(class_basename($column['name'])));
        $defaultIndexName = Str::snake(class_basename($column['name'])) . '_id';
        $referencesTable = $column['referencesTable'] ?? $defaultTableName;
        $referencedTableIndexName = $column['referencedTableIndexName'] ?? $defaultIndexName;

        if (isset($column['referencesTable']) && isset($column['referencedTableIndexName'])) {
            $attributes[] = "->constrained('{$referencesTable}', '{$referencedTableIndexName}')";
        } else if (isset($column['referencesTable'])) {
            $attributes[] = "->constrained('{$referencesTable}')";
        } else {
            $attributes[] = "->constrained()";
        }

        if ($column['onDelete'] ?? false) {
            $attributes[] = "->onDelete('{$column['onDelete']}')";
        }
        if ($column['onUpdate'] ?? false) {
            $attributes[] = "->onUpdate('{$column['onUpdate']}')";
        }

        return implode('', $attributes);
    }
}
