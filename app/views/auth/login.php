<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $escape($title) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; min-height: 100vh; display: grid; place-items: center; background: #f3f4f6; color: #111827; }
        .card { width: min(420px, calc(100% - 32px)); padding: 28px; border: 1px solid #d1d5db; border-radius: 12px; background: #fff; }
        h1 { margin-top: 0; }
        label { display: block; margin-top: 14px; font-weight: 700; }
        input { width: 100%; margin-top: 6px; padding: 10px; border: 1px solid #9ca3af; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; margin-top: 20px; padding: 11px; border: 0; border-radius: 6px; color: #fff; background: #1d4ed8; cursor: pointer; }
        .error, .notice { padding: 10px; border-radius: 6px; }
        .error { color: #991b1b; background: #fee2e2; }
        .notice { color: #1e3a8a; background: #dbeafe; }
    </style>
</head>
<body>
    <main class="card">
        <h1>Product Management Login</h1>
        <p>Log in to access the Lab 5 CRUD application.</p>
        <?php if (!empty($error)): ?><p class="error"><?= $escape($error) ?></p><?php endif; ?>
        <?php if (!empty($notice)): ?><p class="notice"><?= $escape($notice) ?></p><?php endif; ?>
        <form method="post" action="<?= $escape(site_url('login')) ?>">
            <label for="username">Username</label>
            <input id="username" name="username" value="<?= $escape($username ?? '') ?>" required>
            <label for="password">Password</label>
            <input id="password" name="password" type="password" required>
            <button type="submit">Log in</button>
        </form>
    </main>
</body>
</html>
