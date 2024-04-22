<?php

namespace MWGuerra\AppDesign\Utilities;

class EnvFileHandler extends FileHandler {
    protected function serializeData($data): string {
        $envContent = "";
        foreach ($data as $key => $value) {
            // Prepare the value for insertion into the .env file
            $formattedValue = $this->prepareValue($value);
            $envContent .= "{$key}={$formattedValue}\n";
        }
        return $envContent;
    }

    /**
     * Prepares the value to be correctly formatted and escaped for the .env file.
     */
    private function prepareValue($value): string {
        // If the value is numeric or boolean (true, false), leave it unquoted
        if (is_numeric($value) || filter_var($value, FILTER_VALIDATE_BOOLEAN) !== false) {
            return $value;
        }

        // Otherwise, the value is treated as a string. Check for the need to escape double quotes
        $value = str_replace('"', '\"', $value);

        // Wrap the value in quotes if it contains spaces or special characters
        if (preg_match('/\s|[{}()]/', $value)) {
            return '"' . $value . '"';
        }

        return $value;
    }

    public function addKeyValue($key, $value) {
        $data = $this->deserializeData($this->readFile());
        $data[$key] = $this->prepareValue($value); // Prepare the value before saving
        $this->writeFile($this->serializeData($data));
    }

    protected function deserializeData($content): array
    {
        $lines = explode("\n", $content);
        $data = [];
        foreach ($lines as $line) {
            if (str_contains($line, '=')) {
                list($key, $value) = explode('=', $line, 2);
                $data[trim($key)] = trim($value);
            }
        }
        return $data;
    }

    public function getValue($key) {
        $data = $this->deserializeData($this->readFile());
        return $data[$key] ?? null;
    }

    public function deleteKey($key): bool
    {
        $data = $this->deserializeData($this->readFile());
        if (array_key_exists($key, $data)) {
            unset($data[$key]);
            $this->writeFile($this->serializeData($data));
            return true;
        }
        return false;
    }
}

// // Example usage:
// $envHandler = new EnvFileHandler('.env'); // Assume dry run is handled in the writeFile method if needed
//
// // Add or update an environment variable
// $envHandler->addKeyValue('API_KEY', '123456"789'); // Demonstrating escaping quotes
// $envHandler->addKeyValue('SERVICE_URL', 'https://example.com');
// $envHandler->addKeyValue('MESSAGE', 'Hello World! This is a "quoted" message.');
//
// $envHandler = new EnvFileHandler('.env', true); // Enable dry run for testing
//
// // Add or update an environment variable
// $envHandler->addKeyValue('API_KEY', '123456789');
//
// // Get the value of an environment variable
// echo $envHandler->getValue('API_KEY');
//
// // Delete an environment variable
// $envHandler->deleteKey('API_KEY');
