<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
require_once __DIR__ . '/support/Schema.php';

class Lab6_create_refresh_tokens_table
{
    public function up()
    {
        $schema = new Lab6Schema();
        if ($schema->tableExists('refresh_tokens')) {
            $schema->addColumn(__CLASS__, 'refresh_tokens', 'access_token_hash', ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true]);
            return;
        }
        if ($schema->sqlite) {
            $schema->raw('CREATE TABLE refresh_tokens (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                token TEXT NOT NULL,
                expires_at DATETIME NOT NULL,
                jti VARCHAR(255) NOT NULL,
                access_token_hash VARCHAR(64) NULL
            )');
            $schema->raw('CREATE INDEX lab6_refresh_tokens_user_idx ON refresh_tokens(user_id)');
        } else {
            $schema->forge()->add_field([
                'id' => ['type' => 'INT', 'auto_increment' => true],
                'user_id' => ['type' => 'INT'],
                'token' => ['type' => 'TEXT'],
                'expires_at' => ['type' => 'DATETIME'],
                'jti' => ['type' => 'VARCHAR', 'constraint' => 255],
                'access_token_hash' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            ])->add_key('id', true)->add_key('user_id')->create_table('refresh_tokens');
        }
        $schema->remember(__CLASS__, 'refresh_tokens', 'table');
    }

    public function down() { (new Lab6Schema())->undo(__CLASS__, 'refresh_tokens', ['access_token_hash']); }
}
