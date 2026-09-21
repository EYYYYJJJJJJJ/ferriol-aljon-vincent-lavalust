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
<body>
<div class="app-shell">
    <nav class="topbar">
        <a class="brand-simple" href="<?= $escape(site_url('products')) ?>">Activity 5</a>
        <div class="user-nav">
            <span class="signed-in">Signed in as <strong><?= $escape($username) ?></strong></span>
            <a class="button button-ghost button-small" href="<?= $escape(site_url('logout')) ?>">Log out</a>
        </div>
    </nav>

    <main class="page-content">
        <header class="page-header">
            <div>
                <h1>Product Management</h1>
                <p>Manage your products and inventory.</p>
            </div>
            <a class="button button-primary" href="<?= $escape(site_url('products/create')) ?>">
                <span aria-hidden="true">+</span> Add product
            </a>
        </header>

        <?php if (!empty($notice)): ?><p class="alert alert-success"><?= $escape($notice) ?></p><?php endif; ?>
        <?php if (!empty($error)): ?><p class="alert alert-error"><?= $escape($error) ?></p><?php endif; ?>

        <section class="table-card">
            <div class="section-heading">
                <h2>Products</h2>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th class="actions-heading">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($products)): ?>
                        <tr><td class="empty-state" colspan="5">No products yet. Add your first product to get started.</td></tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td class="product-title"><?= $escape($product['product_name']) ?></td>
                                <td class="description-cell"><?= $escape($product['description']) ?></td>
                                <td class="price-cell">₱<?= $escape(number_format((float) $product['price'], 2)) ?></td>
                                <td><?= $escape($product['quantity']) ?></td>
                                <td>
                                    <div class="row-actions">
                                        <a class="text-action" href="<?= $escape(site_url('products/edit/' . (int) $product['id'])) ?>">Edit</a>
                                        <form method="post" action="<?= $escape(site_url('products/delete/' . (int) $product['id'])) ?>" onsubmit="return confirm('Delete this product?');">
                                            <button class="text-action text-danger" type="submit">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
</body>
</html>
