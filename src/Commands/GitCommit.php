<?php

namespace MWGuerra\AppDesign\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class GitCommit extends Command
{
    protected $signature = 'app-design:git-commit 
                        {message=work in progress : Commit message}
                        {initial-commit-message? : Initial commit message when creating a new repository}';
    protected $description = 'Initializes a git repository if not present, commits all existing files, and returns the commit hash.';

    public function handle(): ?int
    {
        $commitMessage = $this->argument('message');
        $initialCommitMessage = $this->argument('initial-commit-message');

        // Check if .git directory exists
        if (!is_dir(base_path('.git'))) {
            $this->runGitCommand(['init']);
            $this->info('Git repository initialized.');

            // Use the initial commit message if provided, otherwise use the normal message
            $commitMessage = $initialCommitMessage ?? $commitMessage;
        } else {
            $this->info('Git repository already initialized.');

            // Determine if the repository is empty (no commits yet)
            $gitLogOutput = $this->runGitCommand(['log']);
            if (str_contains($gitLogOutput, 'fatal: your current branch')) {
                // If no commits, allow the use of initial commit message
                $commitMessage = $initialCommitMessage ?? $commitMessage;
            }
        }

        // Add all files and commit
        $this->runGitCommand(['add', '.']);
        $commitOutput = $this->runGitCommand(['commit', '--allow-empty', '-m', $commitMessage]);

        // Extract commit hash from output
        if (preg_match('/\b[0-9a-f]{5,40}\b/', $commitOutput, $matches)) {
            $commitHash = $matches[0];
            $this->info("Commit successful: {$commitHash}");
            return 0;
        } else {
            $this->error("Commit failed or no changes to commit.");
            return 1;
        }
    }

    protected function runGitCommand(array $command): string
    {
        $process = new Process(['git', ...$command], base_path(), null, null, null);
        try {
            $process->mustRun();
            return $process->getOutput();
        } catch (ProcessFailedException $exception) {
            $this->error('Git command failed: ' . $exception->getMessage());
            return '';
        }
    }
}
