<?php

namespace MWGuerra\AppDesign\Utilities;

class FileHandler {
    protected string $filePath;
    protected bool $dryRun = false;

    public function __construct(string $filePath, bool $dryRun = false) {
        $this->filePath = $filePath;
        $this->dryRun = $dryRun;
    }

    public function setDryRun(): void
    {
        $this->dryRun = true;
    }

    protected function readFile(): false|string
    {
        if (!file_exists($this->filePath)) {
            throw new \Exception("The specified file does not exist: {$this->filePath}");
        }
        return file_get_contents($this->filePath);
    }

    protected function writeFile($content): void
    {
        if ($this->dryRun) {
            echo "Dry run enabled. Output:\n";
            var_dump($content);
        } else {
            file_put_contents($this->filePath, $content);
        }
    }

    // Placeholder methods to be implemented by child classes
    protected function serializeData($data) {
        // To be overridden
    }

    protected function deserializeData($content) {
        // To be overridden
    }
}
