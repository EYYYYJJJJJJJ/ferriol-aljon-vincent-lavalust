<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/** Small MySQL/SQLite adapter for the Lab 6 migrations and reversible ownership. */
class Lab6Schema
{
    private $lava;
    public $sqlite;

    public function __construct()
    {
        $this->lava = lava_instance();
        $this->sqlite = (database_config()['main']['driver'] ?? 'mysql') === 'sqlite';
        $this->lava->call->dbforge();
        $this->raw('CREATE TABLE IF NOT EXISTS lab6_schema_changes (
            migration_name VARCHAR(100) NOT NULL,
            object_name VARCHAR(100) NOT NULL,
            change_type VARCHAR(20) NOT NULL,
            PRIMARY KEY (migration_name, object_name, change_type)
        )');
    }

    public function raw($sql, $parameters = []) { return $this->lava->db->raw($sql, $parameters); }
    public function forge() { return $this->lava->dbforge; }

    public function tableExists($table)
    {
        if (!$this->sqlite) return $this->forge()->table_exists($table);
        return (bool) $this->raw("SELECT 1 FROM sqlite_master WHERE type='table' AND name=:name", ['name' => $table])->fetchColumn();
    }

    public function columnExists($table, $column)
    {
        if (!$this->sqlite) return $this->forge()->column_exists($table, $column);
        foreach ($this->raw('PRAGMA table_info(' . $this->identifier($table) . ')')->fetchAll(PDO::FETCH_ASSOC) as $field) {
            if ($field['name'] === $column) return true;
        }
        return false;
    }

    public function remember($migration, $object, $type)
    {
        $verb = $this->sqlite ? 'INSERT OR IGNORE' : 'INSERT IGNORE';
        $this->raw("$verb INTO lab6_schema_changes (migration_name, object_name, change_type) VALUES (:migration, :object, :type)",
            ['migration' => $migration, 'object' => $object, 'type' => $type]);
    }

    public function owns($migration, $object, $type)
    {
        return (bool) $this->raw('SELECT 1 FROM lab6_schema_changes WHERE migration_name=:migration AND object_name=:object AND change_type=:type',
            ['migration' => $migration, 'object' => $object, 'type' => $type])->fetchColumn();
    }

    public function forget($migration)
    {
        $this->raw('DELETE FROM lab6_schema_changes WHERE migration_name=:migration', ['migration' => $migration]);
    }

    public function addColumn($migration, $table, $name, $details)
    {
        if ($this->columnExists($table, $name)) return;
        if ($this->sqlite) {
            $sql = 'ALTER TABLE ' . $this->identifier($table) . ' ADD COLUMN ' . $this->identifier($name) . ' ' . $details['type'];
            if (isset($details['constraint'])) $sql .= '(' . $details['constraint'] . ')';
            $sql .= !empty($details['null']) ? ' NULL' : ' NOT NULL';
            if (array_key_exists('default', $details)) {
                $sql .= $details['default'] === null ? ' DEFAULT NULL' : " DEFAULT '" . str_replace("'", "''", $details['default']) . "'";
            }
            $this->raw($sql);
        } else {
            $this->forge()->add_column($table, [$name => $details]);
        }
        $this->remember($migration, $table . '.' . $name, 'column');
    }

    public function undo($migration, $table, array $columns = [])
    {
        if ($this->owns($migration, $table, 'table')) {
            $this->raw('DROP TABLE IF EXISTS ' . $this->identifier($table));
        } elseif ($this->tableExists($table)) {
            foreach (array_reverse($columns) as $column) {
                if ($this->owns($migration, $table . '.' . $column, 'column') && $this->columnExists($table, $column)) {
                    $this->raw('ALTER TABLE ' . $this->identifier($table) . ' DROP COLUMN ' . $this->identifier($column));
                }
            }
        }
        $this->forget($migration);
    }

    private function identifier($name)
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name)) throw new InvalidArgumentException('Invalid schema identifier.');
        return '`' . $name . '`';
    }
}
