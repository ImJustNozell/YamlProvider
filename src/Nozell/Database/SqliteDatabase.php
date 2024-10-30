<?php

namespace Nozell\Database;

use SQLite3;
use RuntimeException;
use Exception;

class SqliteDatabase extends AbstractDatabase
{
    private ?SQLite3 $db = null;
    private static $persistentDb = null;
    private const MAX_ATTEMPTS = 5;

    public function __construct(string $filePath, bool $useCache = true)
    {
        parent::__construct($filePath, $useCache);

        $this->initializeDatabase($filePath);
    }

    private function initializeDatabase(string $filePath): void
    {
        $attempts = 0;

        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                throw new RuntimeException("Could not create database directory: $directory.");
            }
        }

        if (!file_exists($filePath)) {
            if (!touch($filePath)) {
                throw new RuntimeException("Error: Could not create database file: $filePath.");
            }
        }

        if (!is_writable($filePath)) {
            throw new RuntimeException("Error: Database file does not have write permissions: $filePath.");
        }

        while ($attempts < self::MAX_ATTEMPTS) {
            try {
                if (self::$persistentDb === null) {
                    self::$persistentDb = new SQLite3($filePath);
                }
                $this->db = self::$persistentDb;

                if (!$this->verifyDatabaseIntegrity()) {
                    throw new RuntimeException("Integrity error: The database file is corrupt.");
                }

                $this->initializeTables();

                return;
            } catch (Exception $e) {
                $attempts++;
                sleep(2);
            }
        }

        throw new RuntimeException("Could not connect to database after " . self::MAX_ATTEMPTS . " intentos.");
    }

    private function verifyDatabaseIntegrity(): bool
    {
        if ($this->db === null) {
            return false;
        }
        $result = $this->db->querySingle("PRAGMA integrity_check");
        return $result === "ok";
    }

    private function initializeTables(): void
    {
        if ($this->db === null) {
            throw new RuntimeException("The database is not initialized.");
        }

        $result = $this->db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='data'");
        if (!$result) {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS data (
                    section TEXT NOT NULL,
                    key TEXT NOT NULL,
                    value TEXT NOT NULL,
                    PRIMARY KEY (section, key)
                );
            ");
        }
    }

    private function ensureDbIsInitialized(): void
    {
        if ($this->db === null) {
            $this->initializeDatabase($this->filePath);
        }
    }

    protected function loadFromFile(): array
    {
        $this->ensureDbIsInitialized();

        $result = $this->db->query("SELECT section, key, value FROM data");
        $data = [];

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $data[$row['section']][$row['key']] = json_decode($row['value'], true);
        }

        return $data;
    }

    protected function saveToFile(array $data): void
    {
        $this->ensureDbIsInitialized();

        foreach ($data as $section => $values) {
            foreach ($values as $key => $value) {
                $stmt = $this->db->prepare("INSERT OR REPLACE INTO data (section, key, value) VALUES (:section, :key, :value)");
                $stmt->bindValue(':section', $section, SQLITE3_TEXT);
                $stmt->bindValue(':key', $key, SQLITE3_TEXT);
                $stmt->bindValue(':value', json_encode($value), SQLITE3_TEXT);
                $stmt->execute();
            }
        }
    }

    protected function saveEntry(string $section, string $key, $value): void
    {
        $this->ensureDbIsInitialized();

        $stmt = $this->db->prepare("INSERT OR REPLACE INTO data (section, key, value) VALUES (:section, :key, :value)");
        $stmt->bindValue(':section', $section, SQLITE3_TEXT);
        $stmt->bindValue(':key', $key, SQLITE3_TEXT);
        $stmt->bindValue(':value', json_encode($value), SQLITE3_TEXT);
        $stmt->execute();
    }

    protected function getEntry(string $section, string $key)
    {
        $this->ensureDbIsInitialized();

        $stmt = $this->db->prepare("SELECT value FROM data WHERE section = :section AND key = :key");
        $stmt->bindValue(':section', $section, SQLITE3_TEXT);
        $stmt->bindValue(':key', $key, SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ? json_decode($row['value'], true) : null;
    }

    protected function deleteEntry(string $section, string $key): void
    {
        $this->ensureDbIsInitialized();

        $stmt = $this->db->prepare("DELETE FROM data WHERE section = :section AND key = :key");
        $stmt->bindValue(':section', $section, SQLITE3_TEXT);
        $stmt->bindValue(':key', $key, SQLITE3_TEXT);
        $stmt->execute();
    }

    protected function hasSection(string $section): bool
    {
        $this->ensureDbIsInitialized();

        $stmt = $this->db->prepare("SELECT 1 FROM data WHERE section = :section LIMIT 1");
        $stmt->bindValue(':section', $section, SQLITE3_TEXT);
        $result = $stmt->execute();
        return $result->fetchArray(SQLITE3_ASSOC) !== false;
    }

    protected function getKeys(string $section): array
    {
        $this->ensureDbIsInitialized();

        $stmt = $this->db->prepare("SELECT key FROM data WHERE section = :section");
        $stmt->bindValue(':section', $section, SQLITE3_TEXT);
        $result = $stmt->execute();
        $keys = [];

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $keys[] = $row['key'];
        }

        return $keys;
    }

    protected function getSections(): array
    {
        $result = $this->db->query("SELECT DISTINCT section FROM data");
        $sections = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $sections[] = $row['section'];
        }
        return $sections;
    }
}
