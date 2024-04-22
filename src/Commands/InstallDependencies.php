<?php

namespace MWGuerra\AppDesign\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class InstallDependencies extends Command
{
    protected $signature = 'app-design:install-dependencies
                        {--composer=* : Composer packages to require}
                        {--composer-dev=* : Composer packages to require as development dependencies}
                        {--npm=* : NPM packages to install}
                        {--npm-dev=* : NPM packages to install as development dependencies}
                        {--yarn=* : Yarn packages to install}
                        {--yarn-dev=* : Yarn packages to install as development dependencies}';
    protected $description = 'Automatically installs necessary PHP and Javascript dependencies.';

    public function handle(): void
    {
        // Check for lock files
        $usesNpm = file_exists(base_path('package-lock.json'));
        $usesYarn = file_exists(base_path('yarn.lock'));

        if ($usesNpm && $usesYarn) {
            $this->warn('Both npm and yarn lock files are detected. It\'s not recommended to use both package managers as it may cause conflicts.');
            if (!$this->confirm('Do you want to continue with the current operation anyway?')) {
                return; // Abort the operation
            }
        }

        // Decide which package manager to use based on user input and file existence
        $manager = $this->determinePackageManager($usesNpm, $usesYarn);

        // Install dependencies using the determined package manager
        $this->installDependencies($manager);
    }

    private function determinePackageManager(bool $usesNpm, bool $usesYarn): string
    {
        if ($usesNpm && !$usesYarn) {
            return 'npm';
        } elseif (!$usesNpm && $usesYarn) {
            return 'yarn';
        } elseif ($usesNpm && $usesYarn) {
            return $this->choice('Both npm and yarn are detected. Which one would you like to use?', ['npm', 'yarn'], 0);
        } else {
            // No lock file found, default to npm unless specified by the user
            return $this->choice('No lock file detected. Which package manager would you like to use?', ['npm', 'yarn'], 0);
        }
    }

    private function installDependencies(string $manager): void
    {
        // Composer dependencies
        if ($this->option('composer')) {
            $this->installComposerDependencies($this->option('composer'), false);
        }
        if ($this->option('composer-dev')) {
            $this->installComposerDependencies($this->option('composer-dev'), true);
        }

        // JS dependencies
        if ($manager === 'npm') {
            if ($this->option('npm')) {
                $this->installJsDependencies($this->option('npm'), false, 'npm');
            }
            if ($this->option('npm-dev')) {
                $this->installJsDependencies($this->option('npm-dev'), true, 'npm');
            }
        } else if ($manager === 'yarn') {
            if ($this->option('yarn')) {
                $this->installJsDependencies($this->option('yarn'), false, 'yarn');
            }
            if ($this->option('yarn-dev')) {
                $this->installJsDependencies($this->option('yarn-dev'), true, 'yarn');
            }
        }
    }

    protected function installJsDependencies(array $packages, bool $dev, string $manager): void
    {
        $this->info('Installing JavaScript dependencies' . ($dev ? ' (dev)' : '') . ' using ' . $manager . '...');
        $jsCommand = [$manager, 'add'];
        if ($dev) {
            $jsCommand[] = ($manager === 'npm') ? '--save-dev' : '--dev';
        }
        $jsCommand = array_merge($jsCommand, $packages);
        $this->runProcess($jsCommand);
    }

    protected function installComposerDependencies(array $packages, bool $dev): void
    {
        $this->info('Installing Composer dependencies' . ($dev ? ' (dev)' : '') . '...');
        $composerCommand = ['composer', 'require'];
        if ($dev) {
            $composerCommand[] = '--dev';
        }
        $this->runProcess(array_merge($composerCommand, $packages));
    }

    protected function runProcess(array $command): int
    {
        $process = new Process($command, base_path(), null, null, null);

        try {
            $process->mustRun(function ($type, $buffer) {
                $this->output->write($buffer);
            });
        } catch (ProcessFailedException $exception) {
            $this->error('The command failed: ' . $exception->getMessage());
            return 1;
        }
        return 0;
    }
}
