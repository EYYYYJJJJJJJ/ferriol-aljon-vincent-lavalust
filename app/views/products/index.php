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
        body { font-family: Arial, sans-serif; margin: 0; background: #f3f4f6; color: #111827; }
        .shell { width: min(1100px, calc(100% - 32px)); margin: 0 auto; padding: 28px 0 48px; }
        header { display: flex; justify-content: space-between; gap: 16px; align-items: center; flex-wrap: wrap; }
        h1 { margin: 0; }
        a, button { border-radius: 6px; padding: 9px 13px; text-decoration: none; cursor: pointer; }
        .primary { color: #fff; background: #1d4ed8; }
        .secondary { color: #1e3a8a; background: #dbeafe; }
        .danger { color: #991b1b; background: #fee2e2; border: 1px solid #fecaca; }
        .logout { color: #374151; background: #fff; border: 1px solid #9ca3af; }
        .notice, .error { margin: 20px 0; padding: 12px; border-radius: 6px; }
        .notice { color: #166534; background: #dcfce7; }
        .error { color: #991b1b; background: #fee2e2; }
        table { width: 100%; margin-top: 22px; border-collapse: collapse; background: #fff; }
        th, td { padding: 12px; border: 1px solid #d1d5db; text-align: left; vertical-align: top; }
        th { background: #e5e7eb; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .actions form { display: inline; }
        @media (max-width: 760px) { table { display: block; overflow-x: auto; } }
    </style>
</head>
<body>
<main class="shell">
    <header>
        <div>
            <h1><?= $escape($title) ?></h1>
            <p>Signed in as <?= $escape($username) ?></p>
        </div>
        <div class="actions">
            <a class="primary" href="<?= $escape(site_url('products/create')) ?>">Add product</a>
            <a class="logout" href="<?= $escape(site_url('logout')) ?>">Log out</a>
        </div>
    </header>

    <?php if (!empty($notice)): ?><p class="notice"><?= $escape($notice) ?></p><?php endif; ?>
    <?php if (!empty($error)): ?><p class="error"><?= $escape($error) ?></p><?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Description</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($products)): ?>
            <tr><td colspan="7">No products found.</td></tr>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= $escape($product['id']) ?></td>
                    <td><?= $escape($product['product_name']) ?></td>
                    <td><?= $escape($product['description']) ?></td>
                    <td><?= $escape(number_format((float) $product['price'], 2)) ?></td>
                    <td><?= $escape($product['quantity']) ?></td>
                    <td><?= $escape($product['created_at']) ?></td>
                    <td class="actions">
                        <a class="secondary" href="<?= $escape(site_url('products/edit/' . (int) $product['id'])) ?>">Edit</a>
                        <form method="post" action="<?= $escape(site_url('products/delete/' . (int) $product['id'])) ?>" onsubmit="return confirm('Delete this product?');">
                            <button class="danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</main>
</body>
</html>
