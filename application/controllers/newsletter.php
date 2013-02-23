<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Newsletter extends CI_Controller {
	public function __construct() {
		parent::__construct();

		$this->load->library('session');
		$this->load->helper('form');
		$this->load->helper('url');
		$this->load->library('form_validation');

		// Custom library
		$this->load->helper('newsletter');
	}

	public function register_newsletter() {
		$data = array(
			'success' => 0,
			'message' => '',
			'data' => array(),
		);
		$email = $this->input->get('email');
		if ($email) {
			if (helper_load_newsletter($this->db, $email)) {
				$data['message'] = '邮件已经被注册';
			}
			else {
				$ret = helper_register_newsletter($this->db, $email);
				if ($ret) {
					$data['success'] = 1;
				}
			}

		}
		return $this->output->set_output(json_encode($data));
	}

	//邮件是否已经注册，是则返回1，否则返回0
	public function is_registered() {
		$data = array(
			'success' => 0,
			'message' => '',
			'data' => array(),
		);
		$email = $this->input->get('email');
		if ($email) {
			$newsletter = helper_load_newsletter($this->db, $email);
			if ($newsletter) {
				$data['success'] = 1;
			}
		}
		return $this->output->set_output(json_encode($data));
	}
}