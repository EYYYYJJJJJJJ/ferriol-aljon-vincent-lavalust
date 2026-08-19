# Lab 3 Student Routing, Controllers, Views, and Middleware Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the Lab 3 Student Information flow in the existing LavaLust project with two routes, a controller, two views, session middleware, data passing, and navigation.

**Architecture:** Keep the feature inside the existing application directories. `StudentController` prepares one associative data array and renders the two views; `StudentMiddleware` resolves the existing LavaLust Session library and gates `/student/profile`; `routes.php` loads the middleware config and attaches the alias to the protected route.

**Tech Stack:** PHP 8.5, LavaLust 4.6.0, PHP sessions, PHP built-in web server, PowerShell, PHP CLI.

**Spec:** `docs/superpowers/specs/2026-08-19-lab3-student-routing-design.md`

## Global Constraints

- Use the existing LavaLust project and GitHub repository.
- Store controllers in `app/controllers/`, middleware in `app/middlewares/`, and views in `app/views/`.
- Register middleware aliases in `app/config/middleware.php` using `load_class()`.
- Define routes in `app/config/routes.php` using LavaLust's `::` controller separator.
- Use LavaLust's `site_url()` helper for navigation links.
- Display the confirmed student data exactly: Jhon Joseph Evora, MCC2024-00076, BS Information Technology, 3rd Year, F2, evora.jhonj@minsu.edu.ph.
- Do not modify the framework files under `scheme/`.

---

### Task 1: Add the Lab 3 behavior test

**Files:**
- Create: `tests/lab3_student_flow_test.php`

**Interfaces:**
- Consumes: the expected Lab 3 source files and route/middleware configuration.
- Produces: a repeatable CLI check that fails before the feature exists and passes after implementation.

- [ ] **Step 1: Write the failing test**

Create a PHP CLI test that checks for the required files, method names, route strings, middleware alias, student data, and navigation labels. Exit with a nonzero status when an assertion fails.

```php
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

$controller = file_text($root, 'app/controllers/StudentController.php');
$middleware = file_text($root, 'app/middlewares/StudentMiddleware.php');
$routes = file_text($root, 'app/config/routes.php');
$middleware_config = file_text($root, 'app/config/middleware.php');
$autoload = file_text($root, 'app/config/autoload.php');
$home_view = file_text($root, 'app/views/student/index.php');
$profile_view = file_text($root, 'app/views/student/profile.php');

expect_true(str_contains($controller, 'class StudentController extends Controller'), 'controller class missing');
expect_true(str_contains($controller, 'function index'), 'controller index method missing');
expect_true(str_contains($controller, 'function profile'), 'controller profile method missing');
expect_true(str_contains($controller, "'student_id' => 'MCC2024-00076'"), 'student ID missing');
expect_true(str_contains($controller, "'name' => 'Jhon Joseph Evora'"), 'student name missing');
expect_true(str_contains($controller, "'course' => 'BS Information Technology'"), 'course missing');
expect_true(str_contains($controller, "'year_level' => '3rd Year'"), 'year level missing');
expect_true(str_contains($controller, "'section' => 'F2'"), 'section missing');
expect_true(str_contains($controller, "'email' => 'evora.jhonj@minsu.edu.ph'"), 'email missing');
expect_true(str_contains($routes, "'/student', 'StudentController::index'"), 'student route missing');
expect_true(str_contains($routes, "'/student/profile', 'StudentController::profile'"), 'profile route missing');
expect_true(str_contains($routes, "->middleware('student_access')"), 'profile middleware attachment missing');
expect_true(str_contains($middleware_config, "'student_access'"), 'middleware alias missing');
expect_true(str_contains($middleware, 'class StudentMiddleware'), 'middleware class missing');
expect_true(str_contains($middleware, 'function handle'), 'middleware handle method missing');
expect_true(str_contains($home_view, 'Student Information'), 'home heading missing');
expect_true(str_contains($profile_view, 'Student Profile'), 'profile heading missing');
expect_true(str_contains($home_view, 'site_url('), 'home navigation helper missing');
expect_true(str_contains($profile_view, 'site_url('), 'profile navigation helper missing');
expect_true(str_contains($autoload, "'url'"), 'URL helper is not autoloaded');

echo "PASS: Lab 3 student flow structure is present.\n";
```

- [ ] **Step 2: Run the test to verify it fails**

Run:

```powershell
php tests/lab3_student_flow_test.php
```

Expected: FAIL with a missing `app/controllers/StudentController.php` message.

### Task 2: Implement the controller, middleware, and views

**Files:**
- Create: `app/controllers/StudentController.php`
- Create: `app/middlewares/StudentMiddleware.php`
- Create: `app/views/student/index.php`
- Create: `app/views/student/profile.php`

**Interfaces:**
- Consumes: LavaLust `Controller`, `Session`, `site_url()`, and `$this->call->view()` APIs.
- Produces: `/student` and `/student/profile` page actions plus a session-gated middleware class.

- [ ] **Step 1: Implement `StudentController`**

Use a private `student_data()` method to keep the two public actions focused. `index()` grants the session flag before rendering the home view; `profile()` renders the same student data after the route middleware has granted access.

```php
<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class StudentController extends Controller
{
    private function student_data()
    {
        return [
            'student_id' => 'MCC2024-00076',
            'name' => 'Jhon Joseph Evora',
            'course' => 'BS Information Technology',
            'year_level' => '3rd Year',
            'section' => 'F2',
            'email' => 'evora.jhonj@minsu.edu.ph',
        ];
    }

    public function index()
    {
        $this->session->set_userdata('student_access', true);

        $this->call->view('student/index', [
            'student' => $this->student_data(),
            'title' => 'Evora Student Hub',
        ]);
    }

    public function profile()
    {
        $this->call->view('student/profile', [
            'student' => $this->student_data(),
            'title' => 'Jhon Joseph Evora | Student Profile',
        ]);
    }
}
```

- [ ] **Step 2: Implement `StudentMiddleware`**

Resolve the Session library in the middleware constructor so the session exists before the router invokes the controller. Stop unauthorized requests by redirecting without calling `$next()`.

```php
<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class StudentMiddleware
{
    private $session;

    public function __construct()
    {
        $this->session = load_class('Session', 'libraries');
    }

    public function handle(Closure $next)
    {
        if ($this->session->userdata('student_access') !== true) {
            redirect('student', false, false);
            return null;
        }

        return $next();
    }
}
```

- [ ] **Step 3: Implement the Student Home view**

Render the associative array with escaped values and links for both required routes.

- [ ] **Step 4: Implement the Student Profile view**

Render the profile heading, student details, middleware status text, and the same navigation links with escaped values.

### Task 3: Register middleware, routes, and the URL helper

**Files:**
- Modify: `app/config/middleware.php`
- Modify: `app/config/routes.php`
- Modify: `app/config/autoload.php`

**Interfaces:**
- Consumes: `StudentMiddleware`, `StudentController`, and LavaLust's config/router APIs.
- Produces: the `student_access` middleware alias, `/student`, and `/student/profile`.

- [ ] **Step 1: Register `student_access`**

Set the middleware config array to:

```php
$config['middlewares'] = [
    'student_access' => load_class('StudentMiddleware', 'middlewares'),
];
```

- [ ] **Step 2: Load middleware config and register routes**

Add this before route definitions in `app/config/routes.php`:

```php
lava_instance()->config->load('middleware');

$router->get('/student', 'StudentController::index');
$router->get('/student/profile', 'StudentController::profile')
       ->middleware('student_access');
```

- [ ] **Step 3: Autoload the URL helper**

Set the helper list in `app/config/autoload.php` to:

```php
$autoload['helpers'] = array('url');
```

### Task 4: Verify the Lab 3 flow

**Files:**
- Test: `tests/lab3_student_flow_test.php`
- Inspect: all modified and created PHP files

**Interfaces:**
- Consumes: the complete Lab 3 implementation.
- Produces: passing structural checks, clean PHP lint, and verified HTTP behavior.

- [ ] **Step 1: Run the structure test after implementation**

Run:

```powershell
php tests/lab3_student_flow_test.php
```

Expected: `PASS: Lab 3 student flow structure is present.`

- [ ] **Step 2: Run PHP lint**

Run:

```powershell
php -l app/controllers/StudentController.php
php -l app/middlewares/StudentMiddleware.php
php -l app/config/middleware.php
php -l app/config/routes.php
php -l app/config/autoload.php
php -l app/views/student/index.php
php -l app/views/student/profile.php
```

Expected: no syntax errors for every file.

- [ ] **Step 3: Exercise the routes through the built-in server**

Start the server from the project root:

```powershell
php -S 127.0.0.1:8000 -t public
```

Request `/student` and verify the page shows the student information and both links. Open `/student/profile` in the same browser session and verify the profile page renders. Use a fresh private browser session to request `/student/profile` first and verify it redirects to `/student`.

- [ ] **Step 4: Review the final diff**

Run:

```powershell
git diff -- app/controllers app/config app/middlewares app/views/student tests
git status --short
```

Expected: only the Lab 3 implementation and test files are added or modified; existing PDF assets remain untracked and untouched.
