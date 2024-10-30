<?php

namespace Nozell\Database;

use RuntimeException;

class JsonDatabase extends AbstractDatabase
{
    protected function loadFromFile(): array
    {
        if (file_exists($this->filePath)) {
            $data = json_decode(file_get_contents($this->filePath), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException("Error al leer el archivo JSON: " . json_last_error_msg());
            }
            return $data ?: [];
        }
        return [];
    }

    protected function saveToFile(array $data): void
    {
        if (file_put_contents($this->filePath, json_encode($data, JSON_PRETTY_PRINT)) === false) {
            throw new RuntimeException("Error al escribir en el archivo JSON.");
        }
    }

    protected function saveEntry(string $section, string $key, $value): void
    {
        $data = $this->loadFromFile();
        $data[$section][$key] = $value;
        $this->saveToFile($data);
    }

    protected function getEntry(string $section, string $key)
    {
        $data = $this->loadFromFile();
        return $data[$section][$key] ?? null;
    }

    protected function deleteEntry(string $section, string $key): void
    {
        $data = $this->loadFromFile();
        unset($data[$section][$key]);
        $this->saveToFile($data);
    }
}
