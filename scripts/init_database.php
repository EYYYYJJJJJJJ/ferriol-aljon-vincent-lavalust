<?php

declare(strict_types=1);

$rootPath = dirname(__DIR__);
$runtimePath = $rootPath . '/runtime';
$driverMarkerPath = $runtimePath . '/database_driver';
$sqlitePath = $runtimePath . '/lavalust.sqlite';

if (!is_dir($runtimePath) && !mkdir($runtimePath, 0775, true) && !is_dir($runtimePath)) {
    throw new RuntimeException('Unable to create the runtime directory.');
}

/** Execute every statement in one of the small, semicolon-delimited lab schemas. */
function applySchema(PDO $pdo, string $schemaPath): void
{
    $schema = file_get_contents($schemaPath);
    if ($schema === false) {
        throw new RuntimeException('Unable to read database schema: ' . $schemaPath);
    }

    foreach (array_filter(array_map('trim', explode(';', $schema))) as $statement) {
        $pdo->exec($statement);
    }
}

/** Try the configured Aiven service first. */
function connectToAiven(): ?PDO
{
    $host = getenv('DB_HOST') ?: '';
    $port = getenv('DB_PORT') ?: '3306';
    $database = getenv('DB_DATABASE') ?: (getenv('DB_NAME') ?: '');
    $username = getenv('DB_USERNAME') ?: (getenv('DB_USER') ?: '');
    $password = getenv('DB_PASSWORD') ?: '';
    $sslCa = getenv('DB_SSL_CA') ?: '';

    if ($host === '' || $database === '' || $username === '' || $password === '') {
        fwrite(STDERR, "Aiven settings are incomplete; using SQLite fallback.\n");
        return null;
    }

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5,
    ];

    if ($sslCa !== '' && defined('Pdo\\Mysql::ATTR_SSL_CA')) {
        $options[\Pdo\Mysql::ATTR_SSL_CA] = $sslCa;
    } elseif ($sslCa !== '' && defined('PDO::MYSQL_ATTR_SSL_CA')) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $host,
        $port,
        $database
    );

    for ($attempt = 1; $attempt <= 3; $attempt++) {
        try {
            return new PDO($dsn, $username, $password, $options);
        } catch (PDOException $exception) {
            fwrite(STDERR, sprintf(
                "Aiven connection attempt %d failed: %s\n",
                $attempt,
                $exception->getMessage()
            ));
            if ($attempt < 3) {
                sleep(2);
            }
        }
    }

    fwrite(STDERR, "Aiven is unavailable; using SQLite fallback.\n");
    return null;
}

$pdo = connectToAiven();
$driver = 'mysql';

if ($pdo !== null) {
    applySchema($pdo, $rootPath . '/database/schema.sql');
    fwrite(STDOUT, "Aiven database schema is ready.\n");
} else {
    $driver = 'sqlite';
    $pdo = new PDO('sqlite:' . $sqlitePath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    applySchema($pdo, $rootPath . '/database/schema_sqlite.sql');
    chmod($sqlitePath, 0666);
    fwrite(STDOUT, "SQLite fallback database is ready.\n");
}

if (file_put_contents($driverMarkerPath, $driver, LOCK_EX) === false) {
    throw new RuntimeException('Unable to write the database driver marker.');
}
chmod($driverMarkerPath, 0666);
