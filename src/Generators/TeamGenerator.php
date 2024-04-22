<?php

namespace MWGuerra\AppDesign\Generators;

use MWGuerra\AppDesign\Utilities\PhpClassEditor;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

/**
 * Generator for managing team configurations in a Laravel application.
 */
class TeamGenerator extends Generator
{
    protected array $teams;
    protected PhpClassEditor $editor;
    protected string $serviceProviderPath;
    protected string $vuePath;
    protected string $configPath;

    public function __construct(array $teams, bool $dryRun = false)
    {
        parent::__construct($dryRun);

        $this->teams = $teams;
        $this->serviceProviderPath = app_path('Providers/JetstreamServiceProvider.php');
        $this->vuePath = resource_path('js/Pages');
        $this->configPath = config_path('jetstream.php');
        $this->editor = new PhpClassEditor($this->serviceProviderPath, $dryRun);
    }

    /**
     * Updates the Jetstream service provider with new permissions configuration.
     */
    public function updateServiceProvider(): void
    {
        if ($this->dryRun) {
            $this->log('Dry run: Would update service provider with new permissions.');
            return;
        }

        $newMethod = $this->generatePermissionsMethod();
        $this->editor->replaceMethod('configurePermissions', $newMethod);
        $this->log('Service provider updated with new permissions.');
    }

    /**
     * Placeholder for updating Vue components.
     */
    public function updateVueComponents(): void
    {
        $this->log('Update Vue components method is not implemented.');
    }

    /**
     * Updates application configurations based on team settings.
     */
    public function updateConfigurations(): void
    {
        if (!File::exists($this->configPath)) {
            $this->log('Configuration path does not exist: ' . $this->configPath);
            return;
        }

        $configContent = File::get($this->configPath);
        $newConfigContent = str_replace("'features' => []", $this->generateFeaturesConfig(), $configContent);

        if ($this->dryRun) {
            $this->log('Dry run: Would update configurations in ' . $this->configPath);
            return;
        }

        File::put($this->configPath, $newConfigContent);
        $this->log('Configurations updated in ' . $this->configPath);
    }

    /**
     * Generates a new permissions method content.
     *
     * @return string The new method content.
     */
    protected function generatePermissionsMethod(): string
    {
        $method = "/**\n";
        $method .= " * Configure the roles and permissions that are available within the application.\n";
        $method .= " */\n";
        $method .= "protected function configurePermissions(): void\n";
        $method .= "{\n";
        $method .= "    Jetstream::defaultApiTokenPermissions(['read']);\n\n";

        foreach ($this->teams['roles'] as $role) {
            $permissions = implode("', '", $role['permissions']);
            $method .= "    Jetstream::role('{$role['name']}', '{$role['description']}', [\n";
            $method .= "        '{$permissions}'\n";
            $method .= "    ])->description('{$role['description']}');\n\n";
        }

        $method .= "}\n";
        return $method;
    }

    /**
     * Generates the configuration array for features.
     *
     * @return string Configuration array as a string.
     */
    protected function generateFeaturesConfig(): string
    {
        $features = [];
        foreach ($this->teams['users-default'] as $setting => $value) {
            if ($value) {
                $features[] = "Features::" . strtoupper($setting);
            }
        }

        return "'features' => [" . implode(', ', $features) . "]";
    }

    /**
     * Generates outputs based on the implemented generator logic.
     *
     * @return array An associative array with 'log' and 'files' as keys.
     */
    public function generate(): array
    {
        $this->updateServiceProvider();
        $this->updateVueComponents();
        $this->updateConfigurations();

        return [
            'log' => $this->getLog(),
            'files' => $this->getFiles()
        ];
    }
}
