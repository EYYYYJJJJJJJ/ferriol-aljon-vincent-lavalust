<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$product = $product ?? [];
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
<div class="form-page">
    <nav class="form-nav">
        <a class="brand-simple" href="<?= $escape(site_url('products')) ?>">Activity 5</a>
        <a class="back-link" href="<?= $escape(site_url('products')) ?>">← Back to products</a>
    </nav>

    <main class="form-card">
        <div class="form-heading">
            <h1><?= $escape($title) ?></h1>
            <p><?= $editing ? 'Update the product information below.' : 'Enter the details of the new product.' ?></p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <strong>Please check the form:</strong>
                <ul><?php foreach ($errors as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form class="product-form" method="post" action="<?= $escape($form_action) ?>">
            <div class="field field-full">
                <label for="product_name">Product name</label>
                <input id="product_name" name="product_name" maxlength="100" placeholder="e.g. Mechanical Keyboard" value="<?= $escape($product['product_name'] ?? '') ?>" required>
            </div>
            <div class="field field-full">
                <label for="description">Description</label>
                <textarea id="description" name="description" placeholder="Short product description" required><?= $escape($product['description'] ?? '') ?></textarea>
            </div>
            <div class="field">
                <label for="price">Price (₱)</label>
                <input id="price" name="price" type="number" min="0" step="0.01" placeholder="0.00" value="<?= $escape($product['price'] ?? '') ?>" required>
            </div>
            <div class="field">
                <label for="quantity">Quantity</label>
                <input id="quantity" name="quantity" type="number" min="0" step="1" placeholder="0" value="<?= $escape($product['quantity'] ?? '') ?>" required>
            </div>
            <div class="form-actions field-full">
                <a class="button button-ghost" href="<?= $escape(site_url('products')) ?>">Cancel</a>
                <button class="button button-primary" type="submit"><?= $editing ? 'Save changes' : 'Add product' ?></button>
            </div>
        </form>
    </main>
</div>
</body>
</html>
