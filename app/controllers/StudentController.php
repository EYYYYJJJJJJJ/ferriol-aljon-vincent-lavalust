<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class StudentController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->library('session');
    }

    private function student_data()
    {
        return [
            'student_id' => 'MCC2024-00076',
            'name' => 'Jhon Joseph Evora',
            'course' => 'BS Information Technology',
            'year_level' => '3rd Year',
            'section' => 'F2',
            'email' => 'evora.jhonj@minsu.edu.ph',
        ];
    }

    public function index()
    {
        $this->session->set_userdata('student_access', true);

        $this->call->view('student/index', [
            'student' => $this->student_data(),
            'title' => 'Evora Student Hub',
            'notice' => $this->session->flashdata('student_access_message'),
        ]);
    }

    public function profile()
    {
        $this->call->view('student/profile', [
            'student' => $this->student_data(),
            'title' => 'Jhon Joseph Evora | Student Profile',
        ]);
    }
}
