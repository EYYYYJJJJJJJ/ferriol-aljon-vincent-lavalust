<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
require_once __DIR__ . '/support/Schema.php';

class Lab6_create_products_table
{
    public function up()
    {
        $schema = new Lab6Schema();
        if ($schema->tableExists('products')) return;
        if ($schema->sqlite) {
            $schema->raw('CREATE TABLE products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                product_name VARCHAR(100) NOT NULL,
                description TEXT NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                quantity INTEGER NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            )');
        } else {
            $schema->forge()->add_field([
                'id' => ['type' => 'INT', 'auto_increment' => true],
                'product_name' => ['type' => 'VARCHAR', 'constraint' => 100],
                'description' => ['type' => 'TEXT'],
                'price' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
                'quantity' => ['type' => 'INT'],
                'created_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'],
            ])->add_key('id', true)->create_table('products');
        }
        $schema->remember(__CLASS__, 'products', 'table');
        foreach ([
            ['Mechanical Keyboard', 'Compact keyboard with tactile switches', 1899.00, 15],
            ['Bluetooth Headphones', 'Wireless over-ear headphones with clear sound', 1499.00, 20],
            ['Portable SSD 1TB', 'Fast and compact external solid-state drive', 3299.00, 8],
        ] as $product) {
            $schema->raw('INSERT INTO products (product_name, description, price, quantity) VALUES (?, ?, ?, ?)', $product);
        }
    }

    public function down() { (new Lab6Schema())->undo(__CLASS__, 'products'); }
}
