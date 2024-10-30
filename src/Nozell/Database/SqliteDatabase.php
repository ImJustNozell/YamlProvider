<?php

namespace Nozell\Database;

use RuntimeException;
use SQLite3;

class SqliteDatabase extends AbstractDatabase
{
    private SQLite3 $db;

    public function __construct(string $filePath, bool $useCache = true)
    {
        parent::__construct($filePath, $useCache);
        $this->db = new SQLite3($filePath);

        // Crear tabla si no existe
        $this->db->exec("CREATE TABLE IF NOT EXISTS storage (section TEXT, key TEXT, value TEXT, PRIMARY KEY (section, key))");
    }

    protected function loadFromFile(): array
    {
        $result = $this->db->query("SELECT section, key, value FROM storage");
        $data = [];

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $data[$row['section']][$row['key']] = json_decode($row['value'], true);
        }

        return $data;
    }

    protected function saveToFile(array $data): void
    {
        foreach ($data as $section => $entries) {
            foreach ($entries as $key => $value) {
                $this->saveEntry($section, $key, $value);
            }
        }
    }

    protected function saveEntry(string $section, string $key, $value): void
    {
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO storage (section, key, value) VALUES (:section, :key, :value)");
        $stmt->bindValue(':section', $section);
        $stmt->bindValue(':key', $key);
        $stmt->bindValue(':value', json_encode($value));
        $stmt->execute();
    }

    protected function getEntry(string $section, string $key)
    {
        $stmt = $this->db->prepare("SELECT value FROM storage WHERE section = :section AND key = :key");
        $stmt->bindValue(':section', $section);
        $stmt->bindValue(':key', $key);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        return $result ? json_decode($result['value'], true) : null;
    }

    protected function deleteEntry(string $section, string $key): void
    {
        $stmt = $this->db->prepare("DELETE FROM storage WHERE section = :section AND key = :key");
        $stmt->bindValue(':section', $section);
        $stmt->bindValue(':key', $key);
        $stmt->execute();
    }
}
