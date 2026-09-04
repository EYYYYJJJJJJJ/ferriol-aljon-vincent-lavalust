<?php

$root = dirname(__DIR__);

function expect_true($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

function file_text($root, $relative_path)
{
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative_path);
    expect_true(is_file($path), "missing {$relative_path}");
    return file_get_contents($path);
}

$model = file_text($root, 'app/models/UsersModel.php');
$controller = file_text($root, 'app/controllers/UsersController.php');
$view = file_text($root, 'app/views/users/index.php');
$routes = file_text($root, 'app/config/routes.php');
$database = file_text($root, 'app/config/database.php');
$database_driver = file_text($root, 'scheme/database/Database.php');
$env_example = file_text($root, '.env.example');
$gitignore = file_text($root, '.gitignore');

expect_true(str_contains($model, 'class UsersModel extends Model'), 'UsersModel class missing');
expect_true(str_contains($model, "protected \$table = 'users'"), 'users table configuration missing');
expect_true(str_contains($controller, 'class UsersController extends Controller'), 'UsersController class missing');
expect_true(str_contains($controller, 'UsersModel'), 'UsersModel is not used by controller');
expect_true(str_contains($controller, '->all()'), 'all() call missing');
expect_true(str_contains($controller, "'users/index'"), 'users view is not loaded');
expect_true(str_contains($view, '<table'), 'users table markup missing');
expect_true(str_contains($view, 'foreach'), 'users view is not dynamic');
foreach (['id', 'firstname', 'lastname', 'email', 'username'] as $column) {
    expect_true(str_contains($view, $column), "{$column} column missing from view");
}
expect_true(str_contains($routes, "'/users', 'UsersController::index'"), '/users route missing');
expect_true(str_contains($database, 'DB_HOST'), 'DB_HOST environment variable missing');
expect_true(str_contains($database, 'DB_PORT'), 'DB_PORT environment variable missing');
expect_true(str_contains($database, 'DB_USERNAME'), 'DB_USERNAME environment variable missing');
expect_true(str_contains($database, 'DB_PASSWORD'), 'DB_PASSWORD environment variable missing');
expect_true(str_contains($database, 'DB_DATABASE'), 'DB_DATABASE environment variable missing');
expect_true(str_contains($database, 'DB_SSL_CA'), 'DB_SSL_CA environment variable missing');
expect_true(str_contains($database_driver, 'PDO::MYSQL_ATTR_SSL_CA'), 'MySQL SSL CA support missing');
expect_true(str_contains($env_example, 'DB_DATABASE='), 'DB_DATABASE missing from env example');
expect_true(str_contains($env_example, 'DB_USERNAME='), 'DB_USERNAME missing from env example');
expect_true(str_contains($gitignore, '.env'), '.env is not ignored');

echo "PASS: Lab 4 database MVC structure is present.\n";
