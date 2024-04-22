<?php

namespace MWGuerra\AppDesign\Utilities;

class IniFileHandler extends FileHandler {
    protected function serializeData($data): string {
        $iniContent = '';
        foreach ($data as $section => $values) {
            if (is_array($values)) {
                $iniContent .= "[$section]\n";
                foreach ($values as $key => $value) {
                    $iniContent .= "$key = " . $this->prepareValue($value) . "\n";
                }
            } else {
                $iniContent .= "$section = " . $this->prepareValue($values) . "\n";
            }
        }
        return $iniContent;
    }

    protected function deserializeData($content): array {
        return parse_ini_string($content, true, INI_SCANNER_TYPED) ?: [];
    }

    public function addKeyValue($key, $value, $section = null) {
        $data = $this->deserializeData($this->readFile());
        if ($section) {
            $data[$section][$key] = $value;
        } else {
            $data[$key] = $value;
        }
        $this->writeFile($this->serializeData($data));
    }

    public function getValue($key, $section = null) {
        $data = $this->deserializeData($this->readFile());
        if ($section) {
            return $data[$section][$key] ?? null;
        }
        return $data[$key] ?? null;
    }

    public function deleteKey($key, $section = null): bool {
        $data = $this->deserializeData($this->readFile());
        if ($section && isset($data[$section][$key])) {
            unset($data[$section][$key]);
            $this->writeFile($this->serializeData($data));
            return true;
        } elseif (isset($data[$key])) {
            unset($data[$key]);
            $this->writeFile($this->serializeData($data));
            return true;
        }
        return false;
    }

    private function prepareValue($value): string {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_numeric($value) || is_bool($value)) {
            return $value;
        }
        return '"' . addcslashes($value, '"') . '"';
    }
}

// Example usage:
// $iniHandler = new IniFileHandler('config.ini');
// $iniHandler->addKeyValue('username', 'user', 'database');
// $iniHandler->addKeyValue('password', 'pass', 'database');
// $iniHandler->addKeyValue('port', 3306, 'database');

// $password = $iniHandler->getValue('password', 'database');
// echo "Database password: $password\n";

// $iniHandler->deleteKey('username', 'database');
