<?php defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= html_escape($title ?? 'Users') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; color: #1f2937; }
        h1 { margin-bottom: 1rem; }
        table { border-collapse: collapse; width: 100%; max-width: 900px; }
        th, td { border: 1px solid #cbd5e1; padding: 0.65rem; text-align: left; }
        th { background: #e2e8f0; }
        .empty { padding: 1rem; background: #f8fafc; }
    </style>
</head>
<body>
    <h1><?= html_escape($title ?? 'Users') ?></h1>

    <?php if (empty($users)): ?>
        <p class="empty">No users found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Username</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= html_escape($user['id']) ?></td>
                        <td><?= html_escape($user['firstname']) ?></td>
                        <td><?= html_escape($user['lastname']) ?></td>
                        <td><?= html_escape($user['email']) ?></td>
                        <td><?= html_escape($user['username']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
