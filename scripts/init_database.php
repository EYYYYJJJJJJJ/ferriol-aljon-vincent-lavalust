<?php

declare(strict_types=1);

$host = getenv('DB_HOST') ?: '';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_DATABASE') ?: (getenv('DB_NAME') ?: '');
$username = getenv('DB_USERNAME') ?: (getenv('DB_USER') ?: '');
$password = getenv('DB_PASSWORD') ?: '';
$sslCa = getenv('DB_SSL_CA') ?: '';

if ($host === '' || $database === '' || $username === '' || $password === '') {
    fwrite(STDERR, "Database environment variables are incomplete.\n");
    exit(1);
}

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

if ($sslCa !== '' && defined('PDO::MYSQL_ATTR_SSL_CA')) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
}

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $host,
    $port,
    $database
);

$pdo = null;
for ($attempt = 1; $attempt <= 20; $attempt++) {
    try {
        $pdo = new PDO($dsn, $username, $password, $options);
        break;
    } catch (PDOException $exception) {
        if ($attempt === 20) {
            throw $exception;
        }
        fwrite(STDERR, "Database not ready; retrying in 3 seconds.\n");
        sleep(3);
    }
}

$schemaPath = dirname(__DIR__) . '/database/schema.sql';
$schema = file_get_contents($schemaPath);
if ($schema === false) {
    throw new RuntimeException('Unable to read database schema.');
}

foreach (array_filter(array_map('trim', explode(';', $schema))) as $statement) {
    $pdo->exec($statement);
}

fwrite(STDOUT, "Aiven database schema is ready.\n");
