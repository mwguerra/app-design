<?php

namespace MWGuerra\AppDesign;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use MWGuerra\AppDesign\Generators\ControllerGenerator;
use MWGuerra\AppDesign\Generators\FactoryGenerator;
use MWGuerra\AppDesign\Generators\LayoutGenerator;
use MWGuerra\AppDesign\Generators\MigrationGenerator;
use MWGuerra\AppDesign\Generators\ModelGenerator;
use MWGuerra\AppDesign\Generators\PolicyGenerator;
use MWGuerra\AppDesign\Generators\RequestGenerator;
use MWGuerra\AppDesign\Generators\ResourceGenerator;
use MWGuerra\AppDesign\Generators\RoutesGenerator;
use MWGuerra\AppDesign\Generators\TestGenerator;
use MWGuerra\AppDesign\Generators\ViewGenerator;
use MWGuerra\AppDesign\Utilities\YamlFileHandler;

class AppDesign {
    /**
     * @throws FileNotFoundException
     */
    public function processResources(string $filePath, bool $dryRun = false): array {
        $resources = $this->parseYAMLFile($filePath);
        $output = [];

        foreach ($resources as $resource) {
            $output[] = $this->processResource($resource, $dryRun);
        }

        if (!$dryRun) {
            $this->generateLayout($resources);
            $this->generateRoutes($resources);
        }

        return $output; // Return output for potential logging or further processing
    }


    protected function parseYAMLFile(string $filePath): array {
        $yamlContent = new YamlFileHandler($filePath);
        return $yamlContent->getValue('resources') ?? [];
    }

    /**
     * @throws FileNotFoundException
     */
    protected function processResource(array $resource): array {
        $output = ['resource' => $resource['name'], 'actions' => []];

        if ($resource['name'] !== 'User' && $resource['name'] !== 'Team') {
            $output['actions']['migrations'] = $this->generateMigrations($resource);
            $output['actions']['model'] = $this->generateModel($resource);
            $output['actions']['factory'] = $this->generateFactory($resource);
            $output['actions']['policies'] = $this->generatePolicy($resource);
            $output['actions']['tests'] = $this->generateTest($resource);
        }

        $output['actions']['views'] = $this->generateViews($resource);
        $output['actions']['controllers'] = $this->generateControllers($resource);
        $output['actions']['requests'] = $this->generateRequests($resource);
        $output['actions']['resources'] = $this->generateResources($resource);

        return $output;
    }

    protected function generateMigrations(array $resource): array {
        $migrationGenerator = new MigrationGenerator($resource);
        return $migrationGenerator->generate();
    }

    protected function generateModel(array $resource): array {
        $modelGenerator = new ModelGenerator($resource);
        return $modelGenerator->generate();
    }

    protected function generateFactory(array $resource): array {
        $factoryGenerator = new FactoryGenerator($resource);
        return $factoryGenerator->generate();
    }

    /**
     * @throws \Exception
     */
    protected function generatePolicy(array $resource): array {
        $policyGenerator = new PolicyGenerator($resource);
        return $policyGenerator->generate();
    }

    protected function generateViews(array $resource): array {
        $viewGenerator = new ViewGenerator($resource);
        return $viewGenerator->generate();
    }

    /**
     * @throws \Exception
     */
    protected function generateControllers(array $resource): array {
        $controllerGenerator = new ControllerGenerator($resource);
        return $controllerGenerator->generate();
    }

    protected function generateRequests(array $resource): array {
        $requestGenerator = new RequestGenerator($resource);
        return $requestGenerator->generate();
    }

    protected function generateResources(array $resource): array {
        $resourceGenerator = new ResourceGenerator($resource);
        return $resourceGenerator->generate();
    }

    /**
     * @throws FileNotFoundException
     */
    protected function generateRoutes(array $resources): array {
        $routesGenerator = new RoutesGenerator($resources);
        return $routesGenerator->generate();
    }

    /**
     * @throws FileNotFoundException
     */
    protected function generateTest(array $resources): array {
        $testGenerator = new TestGenerator($resources);
        return $testGenerator->generate();
    }

    protected function generateLayout(array $resources): array {
        $layoutGenerator = new LayoutGenerator($resources);
        return $layoutGenerator->generate();
    }
}
