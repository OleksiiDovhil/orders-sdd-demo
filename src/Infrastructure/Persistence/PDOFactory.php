<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

final class PDOFactory
{
    public static function create(string $dsn): \PDO
    {
        // Parse DATABASE_URL if it's a full URL, otherwise use as DSN
        if (str_starts_with($dsn, 'postgresql://') || str_starts_with($dsn, 'mysql://')) {
            $params = parse_url($dsn);
            $driver = str_starts_with($dsn, 'postgresql://') ? 'pgsql' : 'mysql';
            $dsn = sprintf(
                '%s:host=%s;port=%s;dbname=%s',
                $driver,
                $params['host'] ?? 'localhost',
                $params['port'] ?? ($driver === 'pgsql' ? 5432 : 3306),
                ltrim($params['path'] ?? '', '/')
            );
            $username = $params['user'] ?? null;
            $password = $params['pass'] ?? null;
        } else {
            $username = null;
            $password = null;
        }

        $pdo = new \PDO($dsn, $username, $password);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        return $pdo;
    }
}
