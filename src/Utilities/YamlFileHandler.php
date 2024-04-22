<?php

namespace MWGuerra\AppDesign\Utilities;

use Symfony\Component\Yaml\Yaml;

class YamlFileHandler extends FileHandler
{
    public function __construct($filePath, $dryRun = false) {
        parent::__construct($filePath, $dryRun);
    }

    protected function serializeData($data) {
        return Yaml::dump($data, 2, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
    }

    protected function deserializeData($content) {
        return Yaml::parse($content) ?: [];
    }

    protected function readYaml()
    {
        if (!file_exists($this->filePath)) {
            return [];
        }
        $yamlContent = file_get_contents($this->filePath);
        return $this->deserializeData($yamlContent);
    }

    protected function writeYaml($data): void
    {
        $yamlContent = $this->serializeData($data);
        file_put_contents($this->filePath, $yamlContent);
    }

    public function addKeyValue($key, $value): void
    {
        $data = $this->readYaml();
        $data[$key] = $value;
        $this->writeYaml($data);
    }

    public function getValue($key)
    {
        $data = $this->readYaml();
        return $data[$key] ?? null;
    }

    public function keyExists($key): bool
    {
        $data = $this->readYaml();
        return array_key_exists($key, $data);
    }

    public function deleteKey($key): bool
    {
        $data = $this->readYaml();
        if (array_key_exists($key, $data)) {
            unset($data[$key]);
            $this->writeYaml($data);
            return true;
        }
        return false;
    }
}

// // Example usage:
// // Make sure 'data.yaml' is in your working directory.
// $yamlHandler = new YamlFileHandler('data.yaml');
//
// // Adding different types of values
// $yamlHandler->addKeyValue('stringKey', 'Hello, YAML!');
// $yamlHandler->addKeyValue('numberKey', 456);
// $yamlHandler->addKeyValue('arrayKey', ['orange', 'grape', 'mango']);
//
// // Reading a value
// echo $yamlHandler->getValue('arrayKey')[2]; // Outputs: mango
//
// // Checking if a key exists
// if ($yamlHandler->keyExists('numberKey')) {
//     echo "The key 'numberKey' exists.\n";
// }
//
// // Deleting a key
// if ($yamlHandler->deleteKey('stringKey')) {
//     echo "'stringKey' was deleted.\n";
// }
