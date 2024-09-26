<?php

namespace Nozell\Database;

use pocketmine\utils\Config;
use RuntimeException;

class YamlDatabase
{
    public string $filePath;
    public array $cache = [];
    public bool $useCache;
    public $fileHandle;
    public array $transactionData = [];
    public bool $inTransaction = false;
    private Config $config;

    public function __construct(string $filePath, bool $useCache = true)
    {
        $this->filePath = $filePath;
        $this->useCache = $useCache;

        if ($this->useCache) {
            $this->cache = $this->loadFromFile();
        } else {
            $this->config = new Config($filePath, Config::YAML);
        }

        $this->fileHandle = fopen($this->filePath, 'c+');
        if (!$this->fileHandle) {
            throw new RuntimeException("No se pudo abrir el archivo para lectura/escritura: $filePath");
        }
    }

    public function loadFromFile(): array
    {
        if (file_exists($this->filePath)) {
            return yaml_parse_file($this->filePath) ?: [];
        }
        return [];
    }

    public function saveToFile(array $data): void
    {
        flock($this->fileHandle, LOCK_EX);
        ftruncate($this->fileHandle, 0);
        rewind($this->fileHandle);
        fwrite($this->fileHandle, yaml_emit($data, YAML_UTF8_ENCODING));
        fflush($this->fileHandle);
        flock($this->fileHandle, LOCK_UN);
    }

    public function set(string $section, string $key, $value): void
    {
        if ($this->useCache) {
            $this->cache[$section][$key] = $value;
            if (!$this->inTransaction) {
                $this->saveToFile($this->cache);
            }
        } else {
            $data = $this->config->get($section, []);
            $data[$key] = $value;
            $this->config->set($section, $data);
            $this->config->save();
        }
    }

    public function get(string $section, string $key)
    {
        if ($this->sectionExists($section)) {
            if ($this->useCache) {
                return $this->cache[$section][$key] ?? null;
            } else {
                $data = $this->config->get($section, []);
                return $data[$key] ?? null;
            }
        } else {
            throw new RuntimeException("La sección '$section' no existe.");
        }
    }

    public function sectionExists(string $section): bool
    {
        if ($this->useCache) {
            return isset($this->cache[$section]);
        } else {
            return $this->config->exists($section);
        }
    }

    public function search(string $section, callable $filter): array
    {
        $results = [];
        $data = $this->getAllKeys($section);
        foreach ($data as $key => $value) {
            if ($filter($key, $value)) {
                $results[$key] = $value;
            }
        }
        return $results;
    }

    public function startTransaction(): void
    {
        if (!$this->useCache) {
            throw new RuntimeException("Las transacciones solo son soportadas con el cache habilitado.");
        }
        if ($this->inTransaction) {
            throw new RuntimeException("Ya hay una transacción en curso.");
        }
        $this->transactionData = $this->cache;
        $this->inTransaction = true;
    }

    public function commitTransaction(): void
    {
        if (!$this->inTransaction) {
            throw new RuntimeException("No hay ninguna transacción en curso.");
        }
        $this->transactionData = [];
        $this->saveToFile($this->cache);
        $this->inTransaction = false;
    }

    public function rollbackTransaction(): void
    {
        if (!$this->inTransaction) {
            throw new RuntimeException("No hay ninguna transacción en curso.");
        }
        if (!empty($this->transactionData)) {
            $this->cache = $this->transactionData;
            $this->transactionData = [];
            $this->inTransaction = false;
        }
    }

    public function renameSection(string $oldName, string $newName): void
    {
        if ($this->sectionExists($oldName)) {
            if ($this->useCache) {
                $this->cache[$newName] = $this->cache[$oldName];
                unset($this->cache[$oldName]);
                $this->saveToFile($this->cache);
            } else {
                $data = $this->config->get($oldName, []);
                $this->config->remove($oldName);
                $this->config->set($newName, $data);
                $this->config->save();
            }
        } else {
            throw new RuntimeException("La sección '$oldName' no existe.");
        }
    }

    public function getAllSections(): array
    {
        if ($this->useCache) {
            return array_keys($this->cache);
        } else {
            $data = $this->loadFromFile();
            return array_keys($data);
        }
    }

    public function getAllKeys(string $section): array
    {
        if ($this->sectionExists($section)) {
            if ($this->useCache) {
                return array_keys($this->cache[$section]);
            } else {
                $data = $this->config->get($section, []);
                return array_keys($data);
            }
        } else {
            throw new RuntimeException("La sección '$section' no existe.");
        }
    }

    public function deleteSection(string $section): void
    {
        if ($this->sectionExists($section)) {
            if ($this->useCache) {
                unset($this->cache[$section]);
                $this->saveToFile($this->cache);
            } else {
                $this->config->remove($section);
                $this->config->save();
            }
        } else {
            throw new RuntimeException("La sección '$section' no existe.");
        }
    }
    public function getSection(string $section): array
    {
        if ($this->sectionExists($section)) {
            if ($this->useCache) {
                return $this->cache[$section];
            } else {
                return $this->config->get($section, []);
            }
        } else {
            throw new RuntimeException("La sección '$section' no existe.");
        }
    }

    public function __destruct()
    {
        fclose($this->fileHandle);
    }
}
