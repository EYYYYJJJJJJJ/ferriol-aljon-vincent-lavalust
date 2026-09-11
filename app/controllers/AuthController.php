<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class AuthController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->library('session');
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim((string) ($_POST['username'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $expected_username = (string) (getenv('AUTH_USERNAME') ?: 'admin');
            $expected_password = (string) (getenv('AUTH_PASSWORD') ?: 'admin123');

            if ($username === $expected_username && hash_equals($expected_password, $password)) {
                $this->session->regenerate_on_login();
                $this->session->set_userdata('authenticated_user', $username);
                redirect('products', false, false);
                return;
            }

            $this->session->set_flashdata('auth_error', 'Invalid username or password.');
            $this->session->set_flashdata('auth_username', $username);
            redirect('login', false, false);
            return;
        }

        $this->call->view('auth/login', [
            'title' => 'Lab 5 Login',
            'error' => $this->session->flashdata('auth_error'),
            'notice' => $this->session->flashdata('auth_notice'),
            'username' => $this->session->flashdata('auth_username') ?: '',
        ]);
    }

    public function logout()
    {
        $this->session->unset_userdata('authenticated_user');
        $this->session->set_flashdata('auth_notice', 'You have been logged out.');
        redirect('login', false, false);
    }
}
