<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/**
 * ------------------------------------------------------------------
 * LavaLust - an opensource lightweight PHP MVC Framework
 * ------------------------------------------------------------------
 *
 * MIT License
 *
 * Copyright (c) 2020 Ronald M. Marasigan
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package LavaLust
 * @author Ronald M. Marasigan <ronald.marasigan@yahoo.com>
 * @since Version 1
 * @link https://github.com/ronmarasigan/LavaLust
 * @license https://opensource.org/licenses/MIT MIT License
 */

/*
| -------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------
| Here is where you can register web routes for your application.
|
|
*/
/** @var object $router **/

lava_instance()->config->load('middleware');

$router->get('/', 'Welcome::index');
$router->get('/student', 'StudentController::index');
$router->get('/student/profile', 'StudentController::profile')
       ->middleware('student_access');
$router->get('/users', 'UsersController::index');
$router->match('/login', 'AuthController::login', 'GET|POST');
$router->get('/logout', 'AuthController::logout');
$router->get('/products', 'ProductController::index')->middleware('product_auth');
$router->match('/products/create', 'ProductController::create', 'GET|POST')->middleware('product_auth');
$router->match('/products/edit/{id}', 'ProductController::edit', 'GET|POST')
       ->where_number('id')
       ->middleware('product_auth');
$router->post('/products/delete/{id}', 'ProductController::delete')
       ->where_number('id')
       ->middleware('product_auth');

// Activity 6 JSON API (authentication is enforced inside every protected action).
$router->post('/api/auth/login', 'Lab6ApiController::login');
$router->post('/api/auth/refresh', 'Lab6ApiController::refresh');
$router->get('/api/auth/me', 'Lab6ApiController::me');
$router->post('/api/auth/logout', 'Lab6ApiController::logout');
$router->get('/api/products', 'Lab6ApiController::products');
$router->post('/api/products', 'Lab6ApiController::create');
$router->get('/api/products/{id}', 'Lab6ApiController::product')->where_number('id');
$router->put('/api/products/{id}', 'Lab6ApiController::update')->where_number('id');
$router->patch('/api/products/{id}', 'Lab6ApiController::update')->where_number('id');
$router->delete('/api/products/{id}', 'Lab6ApiController::delete')->where_number('id');
foreach (array('/api/auth/login', '/api/auth/refresh', '/api/auth/me', '/api/auth/logout', '/api/products') as $apiRoute) {
    $router->options($apiRoute, 'Lab6ApiController::preflight');
}
$router->options('/api/products/{id}', 'Lab6ApiController::preflight')->where_number('id');

// The migration controller rejects web requests and is reachable only through CLI.
if (PHP_SAPI === 'cli') {
    $router->get('/migration', 'MigrationController::index');
    $router->get('/migration/run', 'MigrationController::run');
    $router->get('/migration/status', 'MigrationController::status');
    $router->get('/migration/create/{name}', 'MigrationController::create');
    $router->get('/migration/rollback', 'MigrationController::rollback');
    $router->get('/migration/rollback-all', 'MigrationController::rollback_all');
    $router->get('/migration/refresh', 'MigrationController::refresh');
}
