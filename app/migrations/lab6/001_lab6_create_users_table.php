<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
require_once __DIR__ . '/support/Schema.php';

class Lab6_create_users_table
{
    public function up()
    {
        $schema = new Lab6Schema();
        if (!$schema->tableExists('users')) {
            if ($schema->sqlite) {
                $schema->raw('CREATE TABLE users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    firstname VARCHAR(100) NOT NULL,
                    lastname VARCHAR(100) NOT NULL,
                    email VARCHAR(150) NOT NULL UNIQUE,
                    username VARCHAR(100) NOT NULL UNIQUE,
                    password VARCHAR(255) NULL,
                    role VARCHAR(20) NOT NULL DEFAULT \'user\',
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                )');
            } else {
                $schema->forge()->add_field([
                    'id' => ['type' => 'INT', 'auto_increment' => true],
                    'firstname' => ['type' => 'VARCHAR', 'constraint' => 100],
                    'lastname' => ['type' => 'VARCHAR', 'constraint' => 100],
                    'email' => ['type' => 'VARCHAR', 'constraint' => 150, 'unique' => true],
                    'username' => ['type' => 'VARCHAR', 'constraint' => 100, 'unique' => true],
                    'password' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                    'role' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'user'],
                    'created_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'],
                ])->add_key('id', true)->create_table('users');
            }
            $schema->remember(__CLASS__, 'users', 'table');
            return;
        }
        $schema->addColumn(__CLASS__, 'users', 'password', ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true]);
        $schema->addColumn(__CLASS__, 'users', 'role', ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'user']);
        // SQLite cannot add a nonconstant timestamp default to a populated table.
        $schema->addColumn(__CLASS__, 'users', 'created_at', ['type' => 'TIMESTAMP', 'null' => true]);
    }

    public function down()
    {
        (new Lab6Schema())->undo(__CLASS__, 'users', ['password', 'role', 'created_at']);
    }
}
