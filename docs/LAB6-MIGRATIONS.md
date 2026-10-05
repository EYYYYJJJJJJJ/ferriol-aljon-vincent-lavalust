# Laboratory Exercise 6: migrations

- Student: Aljon Vincent E. Ferriol
- Student ID: MCC2024-00052
- Email: ferriol.aljone@minsu.edu.ph

Lab 6 uses LavaLust's `Migration` library with the dedicated directory `app/migrations/lab6/` and tracking table `migrations_lab6`. The historical migrations outside this directory are not restored or run. MySQL uses DBForge; the bundled SQLite fallback uses equivalent SQL because this version of DBForge generates MySQL syntax.

## Apply and inspect

Set `AUTH_USERNAME`, `AUTH_PASSWORD`, and optionally `AUTH_EMAIL` in the environment. Configure the database using the project's normal database settings. For a separate local SQLite database, set `DB_DRIVER=sqlite` and `DB_SQLITE_PATH` to an absolute path before running commands.

```sh
php scripts/init_lab6.php
php lava migrate:status
php lava migrate
php lava make:migration add_example_field
```

The initialization command applies pending migrations, then creates the configured account with a PHP `password_hash()` password. Existing accounts are matched by username; no other user's row is overwritten when an email is already taken. Repeated runs preserve existing products and do not duplicate migration records or the login account. New, empty installations receive three sample products.

The equivalent guide-style commands are:

```sh
php public/index.php migration/run
php public/index.php migration/status
php public/index.php migration/create/add_example_field
```

Migration controller routes are CLI-only. Visiting `/migration/...` in a browser cannot execute them.

## Tables

- `users`: existing identity fields plus nullable `password`, `role` (default `user`), and `created_at`.
- `refresh_tokens`: `id`, `user_id`, `token`, `expires_at`, `jti`, and nullable `access_token_hash` for immediate access-token revocation.
- `products`: `id`, `product_name`, `description`, `price`, `quantity`, and `created_at`.
- `lab6_schema_changes`: records the tables and columns created by Lab 6 so an authorized rollback preserves objects that existed before the exercise.

Legacy users without configured passwords cannot sign into the API until they have a password set. Existing rows are never assigned the sample account's password.

## Rollback practice

Use a disposable development database for rollback and refresh. These commands remove objects created by Lab 6 and therefore also remove data subsequently stored in those objects.

```sh
php lava migrate:rollback
php lava migrate:rollback-all
php lava migrate:refresh
```

The controller allows these commands in development/testing. In other environments an explicit `--force` or `LAB6_ALLOW_DESTRUCTIVE=1` is required. Deployment initialization only migrates forward and never rolls back or refreshes.

```sh
php tests/lab6_migrations_test.php
```

The automated test uses disposable in-memory SQLite databases. It verifies fresh installation, repeated migrations, populated Lab 5 data preservation, rollback, and refresh. The Windows PHP runtime available in this workspace is `.local-stack/xampp/php/php.exe`; substitute that path for `php` if PHP is not on PATH.
