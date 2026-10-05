<?php
/** Execute actual migrations against disposable SQLite databases only. */
define('PREVENT_DIRECT_ACCESS', true);
define('IS_CLI', false);
define('APP_DIR', dirname(__DIR__) . '/app/');

$testConfig = [
    'migration_enabled' => true,
    'migration_table' => 'migrations_lab6',
    'migration_path' => APP_DIR . 'migrations/lab6/',
];
$testLava = null;
function config_item($key) { global $testConfig; return $testConfig[$key] ?? null; }
function database_config() { return ['main' => ['driver' => 'sqlite']]; }
function lava_instance() { global $testLava; return $testLava; }
function expect_lab6($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}

require dirname(__DIR__) . '/scheme/database/DBForge.php';
require dirname(__DIR__) . '/scheme/libraries/Migration.php';

class Lab6TestDatabase {
    public $pdo;
    public function __construct() { $this->pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); }
    public function raw($sql, $parameters = []) { $stmt = $this->pdo->prepare($sql); $stmt->execute($parameters); return $stmt; }
}
class Lab6TestInvoker {
    public function database() { return lava_instance()->db; }
    public function dbforge() { lava_instance()->dbforge = new DBForge(); }
}
class Lab6TestConfig { public function load($name) {} }

function test_context() {
    global $testLava;
    $testLava = (object) ['db' => new Lab6TestDatabase(), 'call' => new Lab6TestInvoker(), 'config' => new Lab6TestConfig()];
    return $testLava->db;
}
function rows($db, $table) { return $db->raw('SELECT * FROM ' . $table . ' ORDER BY id')->fetchAll(PDO::FETCH_ASSOC); }

try {
    $db = test_context();
    $migration = new Migration();
    ob_start();
    $migration->migrate();
    $migration->migrate();
    ob_end_clean();
    expect_lab6(count(rows($db, 'migrations_lab6')) === 3, 'Migrations must apply exactly once.');
    expect_lab6(count(rows($db, 'products')) === 3, 'Fresh products table must contain three sample products.');
    $columns = $db->raw('PRAGMA table_info(users)')->fetchAll(PDO::FETCH_COLUMN, 1);
    foreach (['password', 'role', 'created_at'] as $column) expect_lab6(in_array($column, $columns), 'Missing user column: ' . $column);
    $columns = $db->raw('PRAGMA table_info(refresh_tokens)')->fetchAll(PDO::FETCH_COLUMN, 1);
    expect_lab6(in_array('access_token_hash', $columns), 'Missing immediate token revocation column.');

    ob_start();
    $migration->rollback();
    expect_lab6($db->raw("SELECT COUNT(*) FROM sqlite_master WHERE name='products'")->fetchColumn() == 0, 'Rollback must undo latest products migration.');
    $migration->migrate();
    $migration->refresh();
    expect_lab6(count(rows($db, 'migrations_lab6')) === 3, 'Refresh must reapply all migrations.');
    $migration->rollback_all();
    ob_end_clean();
    expect_lab6(count(rows($db, 'migrations_lab6')) === 0, 'Rollback-all must clear applied versions.');
    foreach (['users', 'products', 'refresh_tokens'] as $table) {
        expect_lab6($db->raw('SELECT COUNT(*) FROM sqlite_master WHERE name=?', [$table])->fetchColumn() == 0, 'Fresh table should be reversible: ' . $table);
    }

    $db = test_context();
    $db->pdo->exec(file_get_contents(dirname(__DIR__) . '/database/schema_sqlite.sql'));
    $originalUsers = rows($db, 'users');
    $originalProducts = rows($db, 'products');
    $migration = new Migration();
    ob_start();
    $migration->migrate();
    $migration->migrate();
    ob_end_clean();
    expect_lab6(rows($db, 'products') === $originalProducts, 'Existing Lab 5 products must be preserved exactly.');
    foreach (rows($db, 'users') as $index => $user) {
        expect_lab6(array_intersect_key($user, $originalUsers[$index]) === $originalUsers[$index], 'Existing user data must be preserved.');
        expect_lab6($user['password'] === null && $user['role'] === 'user', 'Unconfigured legacy users must not get a shared password.');
    }
    ob_start();
    $migration->rollback_all();
    ob_end_clean();
    expect_lab6(rows($db, 'users') === $originalUsers, 'Rollback must restore existing users schema and rows.');
    expect_lab6(rows($db, 'products') === $originalProducts, 'Rollback must preserve preexisting products.');
    echo "PASS: fresh and populated SQLite migrations, repeat runs, rollback, refresh, and existing data preservation.\n";
} catch (Throwable $error) {
    while (ob_get_level()) ob_end_clean();
    fwrite(STDERR, 'FAIL: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
