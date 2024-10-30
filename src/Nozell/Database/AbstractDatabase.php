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

    abstract protected function saveEntry(string $section, string $key, $value): void;

    abstract protected function getEntry(string $section, string $key);

    abstract protected function deleteEntry(string $section, string $key): void;
}
