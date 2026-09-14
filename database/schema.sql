CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    username VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO users (firstname, lastname, email, username) VALUES
('Aljon Vincent', 'Ferriol', 'aljon.ferriol@example.com', 'aljonferriol'),
('Maria', 'Santos', 'maria@example.com', 'mariasantos'),
('Pedro', 'Garcia', 'pedro@example.com', 'pedrogarcia'),
('Ana', 'Reyes', 'ana@example.com', 'anareyes'),
('Jose', 'Mendoza', 'jose@example.com', 'josemendoza');

INSERT INTO products (product_name, description, price, quantity)
SELECT 'Laptop Stand', 'Adjustable aluminum laptop stand', 899.00, 12
WHERE NOT EXISTS (SELECT 1 FROM products);

INSERT INTO products (product_name, description, price, quantity)
SELECT 'Wireless Mouse', 'Compact wireless mouse', 549.00, 25
WHERE (SELECT COUNT(*) FROM products) = 1;

INSERT INTO products (product_name, description, price, quantity)
SELECT 'USB-C Hub', 'Multi-port USB-C adapter', 1299.00, 8
WHERE (SELECT COUNT(*) FROM products) = 2;
