<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class StudentMiddleware
{
    private $session;

    public function __construct()
    {
        $this->session = load_class('Session', 'libraries');
    }

    public function handle(Closure $next)
    {
        if ($this->session->userdata('student_access') !== true) {
            $this->session->set_flashdata(
                'student_access_message',
                'Visit the Student Home page first to unlock this profile session.'
            );
            redirect('student', false, false);
            return null;
        }

        return $next();
    }
}
