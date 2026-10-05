<?php

abstract class Lab6MigrationCommand
{
    protected $action = 'run';

    public function handle($input = null, array $flags = [])
    {
        $route = 'migration/' . $this->action;
        if ($this->action === 'create') {
            if (!is_string($input) || !preg_match('/^[a-z][a-z0-9_]*$/', $input)) {
                fwrite(STDERR, "Usage: php lava make:migration create_example_table\n");
                exit(1);
            }
            $route .= '/' . $input;
        }
        if (isset($flags['force']) && in_array($this->action, ['rollback', 'rollback-all', 'refresh'], true)) {
            putenv('LAB6_ALLOW_DESTRUCTIVE=1');
        }
        $entry = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php';
        passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($entry) . ' ' . escapeshellarg($route), $status);
        exit($status);
    }
}

class Lab6Migrate extends Lab6MigrationCommand
{
    public static $command = 'migrate';
    public static $description = 'Apply pending Lab 6 migrations';
}
class Lab6MigrationStatus extends Lab6MigrationCommand
{
    public static $command = 'migrate:status';
    public static $description = 'Show Lab 6 migration status';
    protected $action = 'status';
}
class Lab6MigrationCreate extends Lab6MigrationCommand
{
    public static $command = 'make:migration';
    public static $description = 'Create a Lab 6 migration file';
    public static $arguments = ['name' => 'Lowercase snake_case name'];
    protected $action = 'create';
}
class Lab6MigrationRollback extends Lab6MigrationCommand
{
    public static $command = 'migrate:rollback';
    public static $description = 'Rollback the latest Lab 6 migration';
    public static $arguments = ['--force' => 'Explicitly permit rollback outside development/testing'];
    protected $action = 'rollback';
}
class Lab6MigrationRollbackAll extends Lab6MigrationCommand
{
    public static $command = 'migrate:rollback-all';
    public static $description = 'Rollback all Lab 6 migrations';
    public static $arguments = ['--force' => 'Explicitly permit rollback outside development/testing'];
    protected $action = 'rollback-all';
}
class Lab6MigrationRefresh extends Lab6MigrationCommand
{
    public static $command = 'migrate:refresh';
    public static $description = 'Rollback and reapply Lab 6 migrations';
    public static $arguments = ['--force' => 'Explicitly permit refresh outside development/testing'];
    protected $action = 'refresh';
}
