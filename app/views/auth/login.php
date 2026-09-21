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
    <link rel="stylesheet" href="/public/css/lab5.css">
</head>
<body class="login-body">
    <main class="login-card">
        <span class="eyebrow">Activity 5</span>
        <h1>Product Login</h1>
        <p class="login-copy">Sign in to manage your product inventory.</p>

        <?php if (!empty($error)): ?><p class="alert alert-error"><?= $escape($error) ?></p><?php endif; ?>
        <?php if (!empty($notice)): ?><p class="alert alert-info"><?= $escape($notice) ?></p><?php endif; ?>

        <form class="login-form" method="post" action="<?= $escape(site_url('login')) ?>">
            <div class="field field-full">
                <label for="username">Username</label>
                <input id="username" name="username" placeholder="Enter your username" value="<?= $escape($username ?? '') ?>" autocomplete="username" required>
            </div>
            <div class="field field-full">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" required>
            </div>
            <button class="button button-primary login-button" type="submit">Sign in</button>
        </form>
    </main>
</body>
</html>
