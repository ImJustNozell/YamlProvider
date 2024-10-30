<?php

namespace Nozell\Database;

class DatabaseFactory
{
    public static function create(string $filePath, string $type = 'yaml', bool $useCache = true): AbstractDatabase
    {
        switch (strtolower($type)) {
            case 'json':
                return new JsonDatabase($filePath, $useCache);
            case 'sqlite':
                return new SqliteDatabase($filePath, $useCache);
            case 'yaml':
            default:
                return new YamlDatabase($filePath, $useCache);
        }
    }
}
