<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class ProductAuthMiddleware
{
    private $session;

    public function __construct()
    {
        $this->session = load_class('Session', 'libraries');
    }

    public function handle(Closure $next)
    {
        if (!$this->session->userdata('authenticated_user')) {
            $this->session->set_flashdata(
                'auth_notice',
                'Please log in to manage products.'
            );
            redirect('login', false, false);
            return null;
        }

        return $next();
    }
}
