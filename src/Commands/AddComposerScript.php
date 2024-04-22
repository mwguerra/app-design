<?php

namespace MWGuerra\AppDesign\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AddComposerScript extends Command
{
    protected $signature = 'app-design:add-composer-script {scriptName} {scriptCommand}';
    protected $description = 'Adds a custom script to composer.json';

    public function handle(): int
    {
        $scriptName = $this->argument('scriptName');
        $scriptCommand = $this->argument('scriptCommand');
        $composerPath = base_path('composer.json');

        if (!File::exists($composerPath)) {
            $this->error('composer.json does not exist at the project root.');
            return 1;
        }

        $composerJson = json_decode(File::get($composerPath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Failed to parse composer.json');
            return 1;
        }

        // Add or update the script
        $composerJson['scripts'][$scriptName] = $scriptCommand;

        // Save the updated JSON back to the file
        File::put($composerPath, json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info('Script added successfully.');
        return 0;
    }
}
