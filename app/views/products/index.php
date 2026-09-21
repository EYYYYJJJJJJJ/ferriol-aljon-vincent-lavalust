<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$productCount = count($products ?? []);
$stockCount = 0;
$inventoryValue = 0.0;
foreach ($products ?? [] as $item) {
    $stockCount += (int) $item['quantity'];
    $inventoryValue += (float) $item['price'] * (int) $item['quantity'];
}
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
        <a class="brand" href="<?= $escape(site_url('products')) ?>">
            <span class="brand-mark">AF</span>
            <span><strong>SimpleStock</strong><small>Activity 5</small></span>
        </a>
        <div class="user-nav">
            <span class="signed-in">Signed in as <strong><?= $escape($username) ?></strong></span>
            <a class="button button-ghost button-small" href="<?= $escape(site_url('logout')) ?>">Log out</a>
        </div>
    </nav>

    <main class="page-content">
        <header class="page-header">
            <div>
                <span class="eyebrow">Product inventory</span>
                <h1>Manage your products</h1>
                <p>Add, update, and monitor your store inventory in one place.</p>
            </div>
            <a class="button button-primary" href="<?= $escape(site_url('products/create')) ?>">
                <span aria-hidden="true">+</span> Add product
            </a>
        </header>

        <?php if (!empty($notice)): ?><p class="alert alert-success"><?= $escape($notice) ?></p><?php endif; ?>
        <?php if (!empty($error)): ?><p class="alert alert-error"><?= $escape($error) ?></p><?php endif; ?>

        <section class="stats-grid" aria-label="Inventory summary">
            <article class="stat-card"><span>Total products</span><strong><?= $escape($productCount) ?></strong></article>
            <article class="stat-card"><span>Items in stock</span><strong><?= $escape($stockCount) ?></strong></article>
            <article class="stat-card"><span>Inventory value</span><strong>₱<?= $escape(number_format($inventoryValue, 2)) ?></strong></article>
        </section>

        <section class="table-card">
            <div class="section-heading">
                <h2>Products</h2>
                <p><?= $escape($productCount) ?> product<?= $productCount === 1 ? '' : 's' ?> in the catalog</p>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th class="actions-heading">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($products)): ?>
                        <tr><td class="empty-state" colspan="6">No products yet. Add your first product to get started.</td></tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <?php
                            $quantity = (int) $product['quantity'];
                            $status = $quantity === 0 ? 'Out of stock' : ($quantity <= 10 ? 'Low stock' : 'In stock');
                            $statusClass = $quantity === 0 ? 'status-out' : ($quantity <= 10 ? 'status-low' : 'status-good');
                            ?>
                            <tr>
                                <td>
                                    <div class="product-name">
                                        <span class="product-icon"><?= $escape(strtoupper(substr($product['product_name'], 0, 1))) ?></span>
                                        <div><strong><?= $escape($product['product_name']) ?></strong><small>#<?= $escape($product['id']) ?></small></div>
                                    </div>
                                </td>
                                <td class="description-cell"><?= $escape($product['description']) ?></td>
                                <td class="price-cell">₱<?= $escape(number_format((float) $product['price'], 2)) ?></td>
                                <td><?= $escape($quantity) ?></td>
                                <td><span class="status-badge <?= $escape($statusClass) ?>"><?= $escape($status) ?></span></td>
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
