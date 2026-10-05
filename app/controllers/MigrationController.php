<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class MigrationController extends Controller
{
    private $finished = false;

    public function __construct()
    {
        if (PHP_SAPI !== 'cli') {
            http_response_code(404);
            exit;
        }
        // The framework's database error renderer exits without an error code.
        // Ensure failed CLI migrations cannot be reported as a successful deploy.
        register_shutdown_function(function () {
            if (!$this->finished) {
                fwrite(STDERR, "Migration command did not complete.\n");
                exit(1);
            }
        });
        parent::__construct();
        $this->call->library('migration');
    }

    public function index() { $this->run(); }
    public function run() { $this->execute('migrate'); }
    public function status() { $this->execute('status'); }
    public function create($name) { $this->execute('create_migration', $name); }
    public function rollback() { $this->destructive('rollback'); }
    public function rollback_all() { $this->destructive('rollback_all'); }
    public function refresh() { $this->destructive('refresh'); }

    private function destructive($method)
    {
        $environment = strtolower((string) config_item('environment'));
        if (!in_array($environment, ['development', 'testing'], true)
            && getenv('LAB6_ALLOW_DESTRUCTIVE') !== '1') {
            fwrite(STDERR, "Rollback and refresh require development/testing or LAB6_ALLOW_DESTRUCTIVE=1.\n");
            $this->finished = true;
            exit(1);
        }
        $this->execute($method);
    }

    private function execute($method, $argument = null)
    {
        try {
            $argument === null ? $this->migration->$method() : $this->migration->$method($argument);
            $this->finished = true;
        } catch (Throwable $error) {
            fwrite(STDERR, 'Migration failed: ' . $error->getMessage() . PHP_EOL);
            $this->finished = true;
            exit(1);
        }
    }
}
