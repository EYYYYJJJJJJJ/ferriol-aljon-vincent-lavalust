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
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f3f4f6; color: #111827; }
        .card { width: min(640px, calc(100% - 32px)); margin: 40px auto; padding: 28px; border: 1px solid #d1d5db; border-radius: 12px; background: #fff; }
        h1 { margin-top: 0; }
        label { display: block; margin-top: 14px; font-weight: 700; }
        input, textarea { width: 100%; margin-top: 6px; padding: 10px; border: 1px solid #9ca3af; border-radius: 6px; box-sizing: border-box; font: inherit; }
        textarea { min-height: 110px; resize: vertical; }
        button, a { display: inline-block; margin-top: 20px; padding: 10px 14px; border: 0; border-radius: 6px; text-decoration: none; cursor: pointer; }
        button { color: #fff; background: #1d4ed8; }
        a { color: #374151; background: #e5e7eb; }
        .errors { padding: 10px 28px; color: #991b1b; background: #fee2e2; border-radius: 6px; }
    </style>
</head>
<body>
<main class="card">
    <h1><?= $escape($title) ?></h1>
    <?php if (!empty($errors)): ?>
        <div class="errors"><ul><?php foreach ($errors as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="post" action="<?= $escape($form_action) ?>">
        <label for="product_name">Product name</label>
        <input id="product_name" name="product_name" maxlength="100" value="<?= $escape($product['product_name'] ?? '') ?>" required>
        <label for="description">Description</label>
        <textarea id="description" name="description" required><?= $escape($product['description'] ?? '') ?></textarea>
        <label for="price">Price</label>
        <input id="price" name="price" type="number" min="0" step="0.01" value="<?= $escape($product['price'] ?? '') ?>" required>
        <label for="quantity">Quantity</label>
        <input id="quantity" name="quantity" type="number" min="0" step="1" value="<?= $escape($product['quantity'] ?? '') ?>" required>
        <button type="submit"><?= $editing ? 'Update product' : 'Save product' ?></button>
        <a href="<?= $escape(site_url('products')) ?>">Cancel</a>
    </form>
</main>
</body>
</html>
