<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Validation extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->library('session');
	}

	public function index()
	{
		//Empty
	}

	public function is_valid_name() {
		$name = $this->input->post('name');
		$query = $this->db->get_where('user', array('name' => $name));
		$data = array(
			'success' => 0,
			'message' => '',
		);
		if ($query->num_rows() > 0) {
			$data['message'] = '用户名已经存在';
		}
		else {
			$data['success'] = 1;
		}
		$this->output->set_output(json_encode($data));
	}

	public function is_valid_email() {
		$email = $this->input->post('email');
		$query = $this->db->get_where('user', array('mail' => $email));
		$data = array(
			'success' => 0,
			'message' => '',
		);
		if ($query->num_rows() > 0) {
			$data['message'] = "邮件已经被占用";
		}
		else {
			$data['success'] = 1;
		}
		$this->output->set_output(json_encode($data));
	}

	public function is_valid_phone() {
		$phone = $this->input->post('phone');
		$query = $this->db->get_where('user', array('phone' => $phone));
		$data = array(
			'success' => 0,
			'message' => '',
		);
		if ($query->num_rows() > 0) {
			$data['message'] = "电话号码已经被占用";
		}
		else {
			$data['success'] = 1;
		}
		$this->output->set_output(json_encode($data));
	}
}