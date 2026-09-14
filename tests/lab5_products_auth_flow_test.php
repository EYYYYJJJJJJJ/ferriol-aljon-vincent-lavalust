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

$model = file_text($root, 'app/models/ProductModel.php');
$controller = file_text($root, 'app/controllers/ProductController.php');
$auth_controller = file_text($root, 'app/controllers/AuthController.php');
$middleware = file_text($root, 'app/middlewares/ProductAuthMiddleware.php');
$middleware_config = file_text($root, 'app/config/middleware.php');
$routes = file_text($root, 'app/config/routes.php');
$login_view = file_text($root, 'app/views/auth/login.php');
$products_view = file_text($root, 'app/views/products/index.php');
$form_view = file_text($root, 'app/views/products/form.php');
$schema = file_text($root, 'database/schema.sql');
$env_example = file_text($root, '.env.example');

expect_true(str_contains($model, 'class ProductModel extends Model'), 'ProductModel class missing');
expect_true(str_contains($model, 'protected $table = \'products\''), 'products table binding missing');
expect_true(str_contains($model, 'product_name'), 'product_name is not fillable');
expect_true(str_contains($controller, 'class ProductController extends Controller'), 'ProductController class missing');
foreach (['index', 'create', 'edit', 'delete'] as $method) {
    expect_true(str_contains($controller, "function {$method}"), "ProductController::{$method} missing");
}
foreach (['all()', 'insert(', 'update(', 'delete('] as $operation) {
    expect_true(str_contains($controller, $operation), "CRUD operation {$operation} missing");
}
expect_true(str_contains($auth_controller, 'AUTH_USERNAME'), 'auth username configuration missing');
expect_true(str_contains($auth_controller, 'authenticated_user'), 'authenticated session key missing');
expect_true(str_contains($middleware, 'authenticated_user'), 'product auth session check missing');
expect_true(str_contains($middleware_config, "'product_auth'"), 'product_auth middleware registration missing');
foreach (["'/login'", "'/logout'", "'/products'", "'/products/create'", "'/products/edit/", "'/products/delete/"] as $route) {
    expect_true(str_contains($routes, $route), "route {$route} missing");
}
expect_true(str_contains($login_view, '<form'), 'login form missing');
expect_true(str_contains($products_view, '<table'), 'products list table missing');
expect_true(str_contains($form_view, 'product_name'), 'product form missing product_name');
foreach (['CREATE TABLE IF NOT EXISTS products', 'description TEXT', 'price DECIMAL(10,2)', 'quantity INT', 'created_at TIMESTAMP'] as $schema_part) {
    expect_true(str_contains($schema, $schema_part), "schema requirement {$schema_part} missing");
}
expect_true(str_contains($env_example, 'AUTH_USERNAME='), 'AUTH_USERNAME missing from env example');
expect_true(str_contains($env_example, 'AUTH_PASSWORD='), 'AUTH_PASSWORD missing from env example');

echo "PASS: Lab 5 products CRUD and authentication structure is present.\n";
