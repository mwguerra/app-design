<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ModelService
{
    /**
     * Get column details for a given model, resource, or resource collection.
     *
     * @param  Model|Resource|ResourceCollection  $modelOrResource
     * @return array
     */
    public static function getColumnsFor($modelOrResource): array
    {
        $model = $modelOrResource instanceof Model ? $modelOrResource : (new $modelOrResource->resource);

        $table = $model->getTable();
        $columns = Schema::getColumnListing($table);
        $casts = $model->getCasts();
        $hidden = $model->getHidden();

        return collect($columns)->reject(function ($column) use ($hidden) {
            return in_array($column, $hidden);
        })->map(function ($column) use ($table, $casts) {
            $type = $casts[$column] ?? Schema::getColumnType($table, $column);

            switch ($type) {
                case 'int':
                case 'integer':
                case 'real':
                case 'float':
                case 'double':
                case 'decimal':
                    $mappedType = 'number';
                    break;
                case 'boolean':
                    $mappedType = 'boolean';
                    break;
                case 'date':
                case 'datetime':
                case 'custom_datetime':
                case 'datetimetz':
                    $mappedType = 'date';
                    break;
                case 'text':
                case 'longText':
                case 'mediumText':
                    $mappedType = 'text';
                    break;
                default:
                    $mappedType = 'string';
            }

            return [
                'name' => ucfirst(str_replace('_', ' ', $column)),
                'key' => $column,
                'type' => $mappedType,
                'defaultValue' => 'N/A',
            ];
        })->values()->all();
    }
}
