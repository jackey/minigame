<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends CI_Controller {

	public function __construct() {
		parent::__construct();

		$this->load->library('session');
		$this->load->helper('form');
		$this->load->helper('url');
		$this->load->library('form_validation');
	}

	public function index()
	{
		$user = $this->session->userdata('user');
		if (empty($is_login)) {
			// Display login view.
			$this->load->view('user_login');
		}
		else {
			// Display game view.
			$this->load->view('minigame');
		}
	}

	public function login(){
		$this->form_validation->set_rules('username', "Username", 'required');
		$this->form_validation->set_rules('password', "Password", 'required');

		if ($this->form_validation->run() === FALSE) {
			$this->load->view('user_login');
		}
		else {
			$user = array('user' => $this->input->post('username'), 'pass' => $this->input->post('password'));
			$this->session->set_userdata(array('user' => $user));			
			redirect('user/minigame');
		}
	}

	public function minigame() {
		$user = $this->session->userdata('user');
		$this->load->view('minigame');
	}
}