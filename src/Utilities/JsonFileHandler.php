<?php

namespace MWGuerra\AppDesign\Utilities;

class JsonFileHandler extends FileHandler {
    public function __construct($filePath, $dryRun = false) {
        parent::__construct($filePath, $dryRun);
    }

    protected function readJson() {
        if (!file_exists($this->filePath)) {
            return [];
        }
        $jsonContent = file_get_contents($this->filePath);
        return json_decode($jsonContent, true) ?: [];
    }

    protected function writeJson($data) {
        $jsonContent = json_encode($data, JSON_PRETTY_PRINT);
        file_put_contents($this->filePath, $jsonContent);
    }

    protected function serializeData($data) {
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    protected function deserializeData($content) {
        return json_decode($content, true) ?: [];
    }

    public function addKeyValue($key, $value): void
    {
        $data = $this->deserializeData($this->readFile());
        $data[$key] = $value;
        $this->writeFile($this->serializeData($data));
    }

    public function getValue($key) {
        $data = $this->deserializeData($this->readFile());
        return $data[$key] ?? null;
    }


    public function keyExists($key) {
        $data = $this->readJson();
        return array_key_exists($key, $data);
    }

    public function deleteKey($key) {
        $data = $this->readJson();
        if (array_key_exists($key, $data)) {
            unset($data[$key]);
            $this->writeJson($data);
            return true;
        }
        return false;
    }

    /**
     * Adds a new item to an array nested in the JSON data identified by dot notation.
     *
     * @param string $dotNotationKey The key in dot notation to identify the nested array.
     * @param mixed $item The item to add to the array.
     */
    public function addItemToArrayByKey(string $dotNotationKey, mixed $item): void {
        $data = $this->readJson();
        // Split the dot notation key into segments.
        $keys = explode('.', $dotNotationKey);

        // Reference to the current level in the data array.
        $current = &$data;

        // Navigate through the data array following the keys.
        foreach ($keys as $key) {
            // If the key doesn't exist or the current level is not an array, create an empty array.
            if (!isset($current[$key]) || !is_array($current[$key])) {
                $current[$key] = [];
            }
            // Move deeper into the data array.
            $current = &$current[$key];
        }

        // Add the item to the targeted array.
        $current[] = $item;

        // Write the modified data back to the JSON file.
        $this->writeJson($data);
    }
}

// // Example usage:
// $jsonHandler = new JsonFileHandler('data.json');
//
// // Assuming 'user.posts' is the dot notation key and we want to add a new post.
// $jsonHandler->addItemToArrayByKey('user.posts', ['title' => 'New Post', 'content' => 'Post content here.']);
//
// echo "New item added successfully to the nested array.";
//
// // Example usage:
// // Assuming 'data.json' is your file. Make sure it exists or the class will create it for you.
// $jsonHandler = new JsonFileHandler('data.json');
//
// // Adding different types of values
// $jsonHandler->addKeyValue('stringKey', 'Hello, world!');
// $jsonHandler->addKeyValue('numberKey', 123);
// $jsonHandler->addKeyValue('arrayKey', ['apple', 'banana', 'cherry']);
//
// // Reading a value
// echo $jsonHandler->getValue('arrayKey')[1]; // Outputs: banana
//
// // Checking if a key exists
// if ($jsonHandler->keyExists('numberKey')) {
//     echo "The key 'numberKey' exists.";
// }
//
// // Deleting a key
// if ($jsonHandler->deleteKey('stringKey')) {
//     echo "'stringKey' was deleted.";
// }
