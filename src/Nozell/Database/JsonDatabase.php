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
                throw new RuntimeException("Error reading JSON file: " . json_last_error_msg());
            }
            return $data ?: [];
        }
        return [];
    }

    protected function saveToFile(array $data): void
    {
        if (file_put_contents($this->filePath, json_encode($data, JSON_PRETTY_PRINT)) === false) {
            throw new RuntimeException("Error writing to JSON file.");
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

    protected function hasSection(string $section): bool
    {
        $data = $this->loadFromFile();
        return isset($data[$section]);
    }

    protected function getKeys(string $section): array
    {
        $data = $this->loadFromFile();
        return array_keys($data[$section] ?? []);
    }

    protected function getSections(): array
    {
        $data = $this->loadFromFile();
        return array_keys($data);
    }

    public function getAllSections(): array
    {
        if ($this->useCache) {
            return array_keys($this->cache);
        } else {
            return $this->getSections();
        }
    }

    public function sectionExists(string $section): bool
    {
        if ($this->useCache) {
            return isset($this->cache[$section]);
        } else {
            return $this->hasSection($section);
        }
    }

    public function getAllKeys(string $section): array
    {
        if ($this->useCache) {
            return array_keys($this->cache[$section] ?? []);
        } else {
            return $this->getKeys($section);
        }
    }

    public function set(string $section, string $key, $value): void
    {
        if ($this->useCache) {
            $this->cache[$section][$key] = $value;
            $this->saveToFile($this->cache);
        } else {
            $this->saveEntry($section, $key, $value);
        }
    }

    public function get(string $section, string $key)
    {
        if ($this->useCache) {
            return $this->cache[$section][$key] ?? null;
        } else {
            return $this->getEntry($section, $key);
        }
    }

    public function delete(string $section, string $key): void
    {
        if ($this->useCache) {
            unset($this->cache[$section][$key]);
            $this->saveToFile($this->cache);
        } else {
            $this->deleteEntry($section, $key);
        }
    }
}
