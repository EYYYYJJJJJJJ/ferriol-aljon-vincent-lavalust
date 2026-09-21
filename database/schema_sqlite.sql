CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    firstname TEXT NOT NULL,
    lastname TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    username TEXT NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_name TEXT NOT NULL,
    description TEXT NOT NULL,
    price NUMERIC NOT NULL,
    quantity INTEGER NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT OR IGNORE INTO users (firstname, lastname, email, username) VALUES
('Aljon Vincent', 'Ferriol', 'aljon.ferriol@example.com', 'aljonferriol'),
('Maria', 'Santos', 'maria@example.com', 'mariasantos'),
('Pedro', 'Garcia', 'pedro@example.com', 'pedrogarcia'),
('Ana', 'Reyes', 'ana@example.com', 'anareyes'),
('Jose', 'Mendoza', 'jose@example.com', 'josemendoza');

INSERT INTO products (product_name, description, price, quantity)
SELECT 'Mechanical Keyboard', 'Compact keyboard with tactile switches', 1899.00, 15
WHERE NOT EXISTS (SELECT 1 FROM products);

INSERT INTO products (product_name, description, price, quantity)
SELECT 'Bluetooth Headphones', 'Wireless over-ear headphones with clear sound', 1499.00, 20
WHERE (SELECT COUNT(*) FROM products) = 1;

INSERT INTO products (product_name, description, price, quantity)
SELECT 'Portable SSD 1TB', 'Fast and compact external solid-state drive', 3299.00, 8
WHERE (SELECT COUNT(*) FROM products) = 2;
