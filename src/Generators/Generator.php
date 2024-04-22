<?php

namespace MWGuerra\AppDesign\Generators;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

/**
 * Base class for all generators, handling common file operations.
 */
abstract class Generator implements GeneratorInterface
{
    protected Filesystem $filesystem;
    protected string $packageBasePath;
    protected array $log = []; // All actions performed by the generator
    protected array $files = []; // All files manipulated in any way by the generator
    protected bool $dryRun;

    /**
     * Constructor for the Generator.
     *
     * @param bool $dryRun Whether the generator should perform actual file manipulations.
     */
    public function __construct(bool $dryRun = false)
    {
        $this->filesystem = new Filesystem();
        $this->packageBasePath = dirname(__DIR__, 2) . '/src/';
        $this->dryRun = $dryRun;
    }

    /**
     * Logs a message to the internal log array.
     *
     * @param string $message The message to log.
     */
    protected function log(string $message): void
    {
        $this->log[] = $message;
    }

    /**
     * Retrieves all logged messages.
     *
     * @return array An array of log messages.
     */
    public function getLog(): array
    {
        return $this->log;
    }

    /**
     * Retrieves all files manipulated by the generator.
     *
     * @return array An array of file paths.
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Ensures that a directory exists, creating it if it does not.
     *
     * @param string $directory The path to the directory to ensure exists.
     */
    protected function ensureDirectoryExists(string $directory): void
    {
        if (!$this->filesystem->isDirectory($directory)) {
            if ($this->dryRun) {
                $this->log("Dry run: Would create directory: {$directory}");
                return;
            }
            $this->filesystem->makeDirectory($directory, 0755, true);
            $this->log("Directory created: {$directory}");
        }
    }

    /**
     * Copies a file from a source path to a destination path.
     *
     * @param string $sourcePath The source file path.
     * @param string $destinationPath The destination file path.
     */
    protected function copyFile(string $sourcePath, string $destinationPath): void
    {
        $this->ensureDirectoryExists(dirname($destinationPath));
        if ($this->dryRun) {
            $this->log("Dry run: Would copy file from {$sourcePath} to {$destinationPath}");
            return;
        }
        $this->filesystem->copy($sourcePath, $destinationPath);
        $this->log("File copied: {$sourcePath} -> {$destinationPath}");
        $this->files[] = $destinationPath;
    }

    /**
     * Searches for a parent directory by name starting from a given path.
     *
     * @param string $startPath The path to start the search from.
     * @param string $targetDirName The name of the directory to find.
     * @return string|null The path to the found directory or null if not found.
     */
    protected function findParentDirectoryByName(string $startPath, string $targetDirName): ?string
    {
        $currentDir = realpath($startPath);
        while ($currentDir !== false) {
            if (basename($currentDir) === $targetDirName) {
                return $currentDir;
            }
            $parentDir = dirname($currentDir);
            if ($parentDir === $currentDir) {
                break;
            }
            $currentDir = $parentDir;
        }
        return null;
    }

    /**
     * Recursively copies a folder from a source path to a destination path.
     *
     * @param string $sourcePath The source directory path.
     * @param string $destinationPath The destination directory path.
     */
    protected function copyFolder(string $sourcePath, string $destinationPath): void
    {
        $this->ensureDirectoryExists($destinationPath);
        if ($this->dryRun) {
            $this->log("Dry run: Would copy folder from {$sourcePath} to {$destinationPath}");
        }
        $this->filesystem->copyDirectory($sourcePath, $destinationPath);
        $this->log("Folder copied: {$sourcePath} -> {$destinationPath}");
    }

    /**
     * Writes content to a file, checking for dry run mode.
     *
     * @param string $path The file path to write to.
     * @param string $content The content to write.
     */
    protected function writeToFile(string $path, string $content): void
    {
        $this->ensureDirectoryExists(dirname($path));
        if ($this->dryRun) {
            $this->log("Dry run: Would write to file: {$path}");
            return;
        }
        $this->filesystem->put($path, $content);
        $this->log("Content written to file: {$path}");
        $this->files[] = $path;
    }

    /**
     * Appends content to a file, handling dry run scenarios.
     *
     * @param string $path The file path to append to.
     * @param string $content The content to append.
     */
    protected function appendToFile(string $path, string $content): void
    {
        $this->ensureDirectoryExists(dirname($path));
        if ($this->dryRun) {
            $this->log("Dry run: Would append to file: {$path}");
            return;
        }
        $this->filesystem->append($path, '\n' . $content);
        $this->log("Content appended to file: {$path}");
        $this->files[] = $path;
    }

    /**
     * Reads content from a file, throwing an exception if the file does not exist.
     *
     * @param string $path The file path to read from.
     * @return string The content of the file.
     * @throws FileNotFoundException if the file does not exist.
     */
    protected function readFromFile(string $path): string
    {
        if (!$this->filesystem->exists($path)) {
            throw new FileNotFoundException("Trying to read a file that does not exist: {$path}");
        }
        $this->files[] = $path;
        return $this->filesystem->get($path);
    }

    /**
     * Replaces placeholders in a stub file and saves or appends the updated content to a destination file.
     *
     * @param string $stubFilePath The path to the stub file.
     * @param string $destinationFilePath The path to the destination file.
     * @param array $replacements An associative array of placeholders and their replacement values.
     * @param bool $appendToDestinationFile Whether to append to the destination file instead of overwriting.
     * @return string The updated content.
     * @throws FileNotFoundException
     */
    protected function replaceInStubAndSave(string $stubFilePath, string $destinationFilePath, array $replacements, bool $appendToDestinationFile = false): string
    {
        $stub = $this->readFromFile($stubFilePath);
        $updatedContent = $this->replaceInStub($stub, $replacements);

        if ($this->dryRun) {
            $action = $appendToDestinationFile ? "append to" : "write to";
            $this->log("Dry run: Would {$action} file: {$destinationFilePath}");
        }

        if ($appendToDestinationFile) {
            $this->appendToFile($destinationFilePath, $updatedContent);
        } else {
            $this->writeToFile($destinationFilePath, $updatedContent);
        }
        return $updatedContent;
    }

    /**
     * Replaces placeholders in the stub content with specified values.
     *
     * @param string $stub The stub content.
     * @param array $replacements An associative array of placeholders and their replacement values.
     * @return string The updated stub content with placeholders replaced.
     */
    protected function replaceInStub(string $stub, array $replacements): string
    {
        foreach ($replacements as $placeholder => $replacement) {
            $stub = str_replace($placeholder, $replacement, $stub);
        }
        return $stub;
    }

    /**
     * Deletes files in a directory that match a specific pattern.
     *
     * This method iterates over all files in the given directory and deletes
     * those that match the provided regular expression pattern. This is often used
     * to manage files dynamically, ensuring that only the necessary files are retained,
     * such as clearing out old generated files in a migration directory.
     *
     * @param string $directory The directory path where files will be checked.
     * @param string $pattern The regex pattern to match against file names.
     */
    protected function deleteFilesMatchingPattern(string $directory, string $pattern): void
    {
        $files = $this->filesystem->files($directory);
        foreach ($files as $file) {
            $filePath = $file->getPathname();
            if (preg_match($pattern, $file->getFilename())) {
                if ($this->dryRun) {
                    $this->log("Dry run: Would delete file: {$filePath}");
                    continue;
                }
                $this->filesystem->delete($filePath);
                $this->log("Deleted file: {$filePath}");
                $this->files[] = $filePath;
            }
        }
    }

    /**
     * Checks if the specified file contains a given string.
     *
     * @param string $filePath The path to the file to check.
     * @param string $content The content to search for within the file.
     * @return bool Returns true if the file contains the specified content, false otherwise.
     * @throws FileNotFoundException if the file does not exist.
     */
    public function fileContains(string $filePath, string $content): bool
    {
        if (!$this->filesystem->exists($filePath)) {
            throw new FileNotFoundException("The file does not exist: {$filePath}");
        }

        $fileContent = $this->filesystem->get($filePath);
        if (strpos($fileContent, $content) !== false) {
            return true;
        }

        return false;
    }

    /**
     * Appends content to a file only if the content does not already exist in the file.
     *
     * @param string $path The file path to append to.
     * @param string $content The content to append.
     * @return void
     * @throws FileNotFoundException
     */
    protected function appendToFileOnce(string $path, string $content): void
    {
        $this->ensureDirectoryExists(dirname($path));
        if ($this->dryRun) {
            $this->log("Dry run: Would conditionally append to file if not present: {$path}");
            return;
        }
        if (!$this->fileContains($path, $content)) {
            $this->filesystem->append($path, "\n" . $content);
            $this->log("Content conditionally appended to file: {$path}");
        } else {
            $this->log("Content not appended, already exists in file: {$path}");
        }
    }

    /**
     * Subclasses must implement this method to provide specific generation logic.
     *
     * @return array An associative array.
     */
    abstract public function generate(): array;
}
