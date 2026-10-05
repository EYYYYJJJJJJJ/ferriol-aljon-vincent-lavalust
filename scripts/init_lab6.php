<?php
/** Apply the Lab 6 migrations and seed only the configured login account. */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$root = dirname(__DIR__);
try {
    if (is_file($root . '/.env')) {
        foreach (file($root . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
            [$name, $value] = array_map('trim', explode('=', $line, 2));
            if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name)) continue;
            if (strlen($value) >= 2 && in_array($value[0], ['"', "'"], true) && $value[-1] === $value[0]) {
                $value = substr($value, 1, -1);
            }
            putenv($name . '=' . $value);
        }
    }

    $entry = $root . '/public/index.php';
    passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($entry) . ' migration/run', $status);
    if ($status !== 0) throw new RuntimeException('The migration command failed.');

    define('PREVENT_DIRECT_ACCESS', true);
    require $root . '/app/config/database.php';
    $settings = $database['main'];
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];
    if ($settings['driver'] === 'sqlite') {
        $pdo = new PDO('sqlite:' . $settings['path'], null, null, $options);
    } else {
        if (!empty($settings['ssl_ca'])) {
            if (defined('Pdo\\Mysql::ATTR_SSL_CA')) {
                $options[\Pdo\Mysql::ATTR_SSL_CA] = $settings['ssl_ca'];
                $options[\Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT] = true;
            } else {
                $options[PDO::MYSQL_ATTR_SSL_CA] = $settings['ssl_ca'];
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
            }
        }
        $dsn = 'mysql:host=' . $settings['hostname'] . ';port=' . ($settings['port'] ?: '3306')
            . ';dbname=' . $settings['database'] . ';charset=utf8mb4';
        $pdo = new PDO($dsn, $settings['username'], $settings['password'], $options);
    }

    $username = getenv('AUTH_USERNAME') ?: 'aljon';
    $password = getenv('AUTH_PASSWORD');
    if ($password === false || $password === '') {
        throw new RuntimeException('Set AUTH_PASSWORD before initializing the Lab 6 login account.');
    }
    $email = getenv('AUTH_EMAIL') ?: 'aljon.ferriol@example.com';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('AUTH_EMAIL must be a valid email address.');
    if (strlen($username) > 100 || strlen($email) > 150) throw new RuntimeException('The configured username or email is too long.');

    $pdo->beginTransaction();
    $lookup = $pdo->prepare('SELECT id, password FROM users WHERE username = :username');
    $lookup->execute(['username' => $username]);
    $user = $lookup->fetch();
    if ($user) {
        if (!$user['password'] || !password_verify($password, $user['password']) || password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            $update = $pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
            $update->execute(['password' => password_hash($password, PASSWORD_DEFAULT), 'id' => $user['id']]);
        }
    } else {
        // An older lab may already own AUTH_EMAIL under another username.
        // Preserve that row and choose a unique email for the new account.
        $emailLookup = $pdo->prepare('SELECT id FROM users WHERE email = :email');
        $baseEmail = $email;
        $attempt = 0;
        do {
            $emailLookup->execute(['email' => $email]);
            $collision = $emailLookup->fetchColumn();
            if ($collision) {
                $attempt++;
                [$local, $domain] = explode('@', $baseEmail, 2);
                $email = substr($local, 0, 40) . '+lab6-' . substr(hash('sha256', $username), 0, 8)
                    . ($attempt > 1 ? '-' . $attempt : '') . '@' . $domain;
                if (strlen($email) > 150 || $attempt > 100) throw new RuntimeException('Cannot allocate a unique seed-account email.');
            }
        } while ($collision);
        $insert = $pdo->prepare('INSERT INTO users (firstname, lastname, email, username, password, role, created_at)
            VALUES (:firstname, :lastname, :email, :username, :password, :role, CURRENT_TIMESTAMP)');
        $insert->execute([
            'firstname' => 'Aljon Vincent', 'lastname' => 'Ferriol', 'email' => $email,
            'username' => $username, 'password' => password_hash($password, PASSWORD_DEFAULT), 'role' => 'user',
        ]);
        if ($attempt > 0) echo "The requested email already belongs to another user; its row was preserved and a unique seed-account email was used.\n";
    }
    $pdo->commit();
    echo "Lab 6 database and configured login account are ready.\n";
} catch (Throwable $error) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, 'Lab 6 initialization failed: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
