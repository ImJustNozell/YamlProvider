<?php

namespace Nozell\Database;

abstract class AbstractDatabase
{
    protected string $filePath;
    protected bool $useCache;
    protected array $cache = [];

    public function __construct(string $filePath, bool $useCache = true)
    {
        $this->filePath = $filePath;
        $this->useCache = $useCache;

        if ($this->useCache) {
            $this->cache = $this->loadFromFile();
        }
    }

    abstract protected function loadFromFile(): array;

    abstract protected function saveToFile(array $data): void;

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

    public function getAllSections(): array
    {
        if ($this->useCache) {
            return array_keys($this->cache);
        } else {
            return $this->getSections();
        }
    }

    abstract protected function saveEntry(string $section, string $key, $value): void;

    abstract protected function getEntry(string $section, string $key);

    abstract protected function deleteEntry(string $section, string $key): void;

    abstract protected function hasSection(string $section): bool;

    abstract protected function getKeys(string $section): array;

    abstract protected function getSections(): array;
}
