<?php

namespace MWGuerra\AppDesign\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use MWGuerra\AppDesign\Facades\AppDesign;

class ProcessResources extends Command
{
    protected $signature = 'app-design:process-resources
                            {file=app-design-resources.yaml : The YAML file to process}
                            {--dry-run : Simulate the process without creating any files}
                            {--seed : Seed the database after migration}
                            {--dependencies : Install dependencies after processing}
                            {--commit : Commit changes after processing}
                            {--composer-scripts : Add or update Composer scripts}
                            {--use-example-yaml : Use a fixed example yaml file for processing}';

    protected $description = 'Processes a YAML file to generate migration files, models, and optionally seeds database, installs dependencies, commits changes, and updates Composer scripts.';

    public function handle(): void {
        $filePath = $this->argument('file');
        if ($this->option('use-example-yaml')) {
            $basePackagePath = dirname(__DIR__, 2);
            $filePath = $basePackagePath . "/tests/stubs/teste3.yaml";
        }

        $dryRun = $this->option('dry-run');
        $shouldSeed = $this->option('seed');
        $installDependencies = $this->option('dependencies');
        $commitChanges = $this->option('commit');
        $updateComposerScripts = $this->option('composer-scripts');

        if (!file_exists($filePath)) {
            $this->error("The specified file does not exist.");
            return;
        }

        $this->info("== Processing YAML File ===========================");
        try {
            $output = AppDesign::processResources($filePath);
            $this->info('All resources have been processed successfully.');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }

        if (!$dryRun) {
            if ($commitChanges) {
                $this->createGitRestorePoint("chore: restore-point: before creating resources from {$filePath}");
            }

            if ($installDependencies) {
                $this->installDependencies();
            }

            if ($updateComposerScripts) {
                $this->addOrUpdateComposerScripts();
            }

            if ($commitChanges) {
                $this->createGitRestorePoint("feat: add resources from {$filePath}");
            }

            if ($shouldSeed || $commitChanges) {
                $this->migrateFreshAndBuild($shouldSeed);
            }
        }
    }

    protected function createGitRestorePoint(string $message): void {
        $statusCode = $this->call('app-design:git-commit', [
            'message' => $message
        ]);

        if ($statusCode === 0) {
            $this->info("Git restore point created.");
        } else {
            $this->error("Git commit failed.");
        }
    }

    protected function installDependencies(): void {
        $this->info("== Installing Dependencies ===========================");
        $this->call('app-design:install-dependencies', [
            '--yarn' => ['@headlessui/vue', '@heroicons/vue'],
        ]);
        $this->info("@headlessui/vue and @heroicons/vue have been installed.");
    }

    protected function addOrUpdateComposerScripts(): void {
        $this->info("== Adding or Updating Composer Scripts ==============");
        $this->call("app-design:add-composer-script", [
            'scriptName' => "update-app-design",
            'scriptCommand' => "php artisan app-design:process-resources {$this->argument('file')} ; npm run build ; php artisan migrate:fresh --seed"
        ]);
        $this->info("Composer scripts have been updated.");
    }

    protected function migrateFreshAndBuild(bool $seed): void {
        $options = [
            '--force' => true
        ];
        if ($seed) {
            $options['--seed'] = true;
        }

        $this->call('migrate:fresh', $options);

        $this->runNpmBuild();

        $this->info("Database migrated and JS resources built.");
    }

    protected function runNpmBuild(): void
    {
        $this->info("Running npm build...");
        exec("npm run build", $output, $return_var);
        if ($return_var !== 0) {
            $this->error("Failed to run npm build.");
        } else {
            $this->info("npm build executed successfully.");
        }
    }
}
