<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/** JSON endpoints for the separate Activity 6 Vue application. */
class Lab6ApiController extends Controller
{
    private $user;
    private $accessHash;

    public function __construct()
    {
        parent::__construct();
        $this->config->load('api');
        header('Cache-Control: no-store');
        header('Vary: Origin');
        if (strlen(config_item('jwt_secret')) < 32 || strlen(config_item('refresh_token_key')) < 32) {
            http_response_code(503);
            header('Content-Type: application/json');
            exit(json_encode(['error' => 'API authentication is not configured.', 'status' => 503]));
        }
        // The API library responds to preflight requests before connecting to DB.
        $this->call->library('api');
        $this->call->database();
    }

    public function preflight() { $this->api->respond([], 204); }

    private function json_body()
    {
        if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== 0) {
            $this->api->respond_error('Send an application/json request.', 415);
        }
        $raw = file_get_contents('php://input', false, null, 0, 131073);
        if (strlen($raw) > 131072) $this->api->respond_error('Request body is too large.', 413);
        $body = json_decode($raw);
        if (json_last_error() !== JSON_ERROR_NONE || !is_object($body)) {
            $this->api->respond_error('A valid JSON object is required.', 400);
        }
        return (array) $body;
    }

    private function refresh_hash($token)
    {
        return hash_hmac('sha256', $token, config_item('refresh_token_key'));
    }

    private function tokens_for(array $user)
    {
        $tokens = $this->api->issue_tokens($user);
        $this->db->raw('UPDATE refresh_tokens SET access_token_hash = ? WHERE token = ?', [
            hash('sha256', $tokens['access_token']), $this->refresh_hash($tokens['refresh_token'])
        ]);
        $tokens['user'] = ['id' => (int) $user['id'], 'username' => $user['username']];
        return $tokens;
    }

    private function authenticate()
    {
        $token = $this->api->get_bearer_token() ?: '';
        $payload = $this->api->validate_jwt($token);
        if (!$payload || ($payload['type'] ?? '') === 'refresh') {
            $this->api->respond_error('Please log in to continue.', 401);
        }
        $this->accessHash = hash('sha256', $token);
        $this->user = $this->db->raw(
            'SELECT u.id, u.username FROM users u INNER JOIN refresh_tokens r ON r.user_id = u.id WHERE r.access_token_hash = ? AND r.user_id = ? AND r.expires_at > ? LIMIT 1',
            [$this->accessHash, $payload['sub'], date('Y-m-d H:i:s')]
        )->fetch(PDO::FETCH_ASSOC);
        if (!$this->user) $this->api->respond_error('Your session has expired. Please log in again.', 401);
        $this->user['id'] = (int) $this->user['id'];
    }

    public function login()
    {
        $this->api->rate_limit('lab6-login-' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 15, 60);
        $body = $this->json_body();
        $username = is_string($body['username'] ?? null) ? trim($body['username']) : '';
        $password = is_string($body['password'] ?? null) ? $body['password'] : '';
        if (strlen($username) > 100 || strlen($password) > 1024) $this->api->respond_error('Invalid username or password.', 401);
        $user = $this->db->raw('SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1', [$username])->fetch(PDO::FETCH_ASSOC);
        // Constant-cost verification also when a username is absent.
        $hash = $user['password'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
        $valid = password_verify($password, $hash);
        if (!$user || !$valid || $password === '') $this->api->respond_error('Invalid username or password.', 401);
        $this->api->respond($this->tokens_for($user));
    }

    public function refresh()
    {
        $this->api->rate_limit('lab6-refresh-' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 60, 60);
        $body = $this->json_body();
        $token = is_string($body['refresh_token'] ?? null) ? $body['refresh_token'] : '';
        $payload = $this->api->validate_jwt($token);
        if (!$payload || ($payload['type'] ?? '') !== 'refresh') $this->api->respond_error('Invalid refresh token.', 401);
        $hashed = $this->refresh_hash($token);
        $this->db->transaction();
        $user = $this->db->raw('SELECT u.id, u.username, u.role FROM users u INNER JOIN refresh_tokens r ON r.user_id = u.id WHERE r.token = ? AND r.user_id = ? AND r.expires_at > ? LIMIT 1', [$hashed, $payload['sub'], date('Y-m-d H:i:s')])->fetch(PDO::FETCH_ASSOC);
        // Conditional deletion makes a refresh token one-use even for concurrent requests.
        $deleted = $this->db->raw('DELETE FROM refresh_tokens WHERE token = ? AND user_id = ? AND expires_at > ?', [$hashed, $payload['sub'], date('Y-m-d H:i:s')])->rowCount();
        if (!$user || $deleted !== 1) {
            $this->db->roll_back();
            $this->api->respond_error('Refresh token expired or revoked.', 401);
        }
        $tokens = $this->tokens_for($user);
        $this->db->commit();
        $this->api->respond($tokens);
    }

    public function me()
    {
        $this->authenticate();
        $this->api->respond(['user' => $this->user]);
    }

    public function logout()
    {
        $this->authenticate();
        // Revoke exactly the authenticated session, not an arbitrary supplied token.
        $this->db->raw('DELETE FROM refresh_tokens WHERE access_token_hash = ? AND user_id = ?', [$this->accessHash, $this->user['id']]);
        $this->api->respond(['message' => 'Logged out successfully.']);
    }

    private function find_product($id)
    {
        $product = $this->db->raw('SELECT id, product_name, description, price, quantity, created_at FROM products WHERE id = ?', [$id])->fetch(PDO::FETCH_ASSOC);
        if (!$product) $this->api->respond_error('Product not found.', 404);
        return $this->format_product($product);
    }

    private function format_product(array $row)
    {
        $row['id'] = (int) $row['id'];
        $row['quantity'] = (int) $row['quantity'];
        $row['price'] = number_format((float) $row['price'], 2, '.', '');
        return $row;
    }

    private function product_data(array $body, $partial = false)
    {
        $data = []; $errors = [];
        foreach (['product_name', 'description', 'price', 'quantity'] as $field) {
            if ($partial && !array_key_exists($field, $body)) continue;
            $value = $body[$field] ?? null;
            if ($field === 'product_name' || $field === 'description') {
                $value = is_string($value) ? trim($value) : '';
                $length = preg_match_all('/./us', $value);
                if ($value === '' || ($field === 'product_name' && $length > 100) || ($field === 'description' && strlen($value) > 65535)) {
                    $errors[$field] = $field === 'product_name' ? 'Enter a product name (1–100 characters).' : 'Enter a description (up to 65535 bytes).';
                }
            } elseif ($field === 'price') {
                if ((!is_string($value) && !is_int($value) && !is_float($value)) || !preg_match('/^\d{1,8}(\.\d{1,2})?$/D', (string) $value) || (float) $value > 99999999.99) {
                    $errors[$field] = 'Enter a price from 0 to 99999999.99 with at most two decimal places.';
                } else $value = number_format((float) $value, 2, '.', '');
            } else {
                if ((!is_int($value) && !is_string($value)) || !preg_match('/^\d{1,10}$/D', (string) $value) || (float) $value > 2147483647) {
                    $errors[$field] = 'Enter a whole-number quantity from 0 to 2147483647.';
                } else $value = (int) $value;
            }
            $data[$field] = $value;
        }
        if ($partial && !$data) $errors['product'] = 'Supply at least one product field.';
        if ($errors) $this->api->respond(['error' => 'Please check the product details.', 'status' => 422, 'errors' => $errors], 422);
        return $data;
    }

    public function products()
    {
        $this->authenticate();
        $rows = $this->db->raw('SELECT id, product_name, description, price, quantity, created_at FROM products ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
        $this->api->respond(['data' => array_map([$this, 'format_product'], $rows)]);
    }

    public function product($id)
    {
        $this->authenticate();
        $this->api->respond(['data' => $this->find_product($id)]);
    }

    public function create()
    {
        $this->authenticate();
        $data = $this->product_data($this->json_body());
        $this->db->table('products')->insert($data);
        $product = $this->find_product($this->db->last_id());
        $this->api->respond(['data' => $product, 'message' => 'Product added successfully.'], 201);
    }

    public function update($id)
    {
        $this->authenticate();
        $this->find_product($id);
        $data = $this->product_data($this->json_body(), $_SERVER['REQUEST_METHOD'] === 'PATCH');
        $assignments = array_map(function ($key) { return $key . ' = ?'; }, array_keys($data));
        $this->db->raw('UPDATE products SET ' . implode(', ', $assignments) . ' WHERE id = ?', array_merge(array_values($data), [$id]));
        $this->api->respond(['data' => $this->find_product($id), 'message' => 'Product updated successfully.']);
    }

    public function delete($id)
    {
        $this->authenticate();
        $deleted = $this->db->raw('DELETE FROM products WHERE id = ?', [$id])->rowCount();
        if (!$deleted) $this->api->respond_error('Product not found.', 404);
        $this->api->respond(['message' => 'Product deleted successfully.']);
    }
}
