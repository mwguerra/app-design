<?php

namespace MWGuerra\AppDesign\Generators;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class RoutesGenerator extends Generator
{
    protected $resources;
    protected $routesFilePath;

    public function __construct(array $resources, bool $dryRun = false)
    {
        parent::__construct($dryRun);
        $this->resources = $resources;
        $this->routesFilePath = base_path('routes/web.php');
    }

    /**
     * @throws FileNotFoundException
     */
    public function generate(): array
    {
        $routes = [];
        $resourceRoutes = $this->buildResourceRoutes();

        // Construct the full routing group with middleware
        $routeDefinition = $this->wrapRoutesWithMiddlewareGroup($resourceRoutes);

        if ($this->dryRun) {
            $this->log("Dry run: Would append routes to {$this->routesFilePath}");
        } else {
            $this->appendToFileOnce($this->routesFilePath, $routeDefinition);
            $this->log("Routes appended to {$this->routesFilePath}");
        }

        return [
            'log' => $this->getLog(),
            'routes' => $routes,
            'files' => $this->getFiles()
        ];
    }

    protected function buildResourceRoutes(): string
    {
        $resourceRoutes = "";
        foreach ($this->resources as $resource) {
            $modelName = Str::studly($resource['name']);
            $modelSlug = Str::kebab(Str::plural($modelName));
            $newRoute = $this->generateResourceRoute($modelName, $modelSlug);
            $resourceRoutes .= $newRoute;
        }
        return $resourceRoutes;
    }

    protected function wrapRoutesWithMiddlewareGroup(string $resourceRoutes): string
    {
        $groupStart = "Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {\n";
        $groupEnd = "});\n";
        return $groupStart . $resourceRoutes . $groupEnd;
    }

    protected function generateResourceRoute(string $modelName, string $modelSlug): string
    {
        return "    Route::resource('{$modelSlug}', \App\Http\Controllers\\{$modelName}Controller::class);\n";
    }
}
