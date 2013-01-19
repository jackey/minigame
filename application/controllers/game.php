<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Game extends CI_Controller {

	public function __construct() {
		parent::__construct();

		$this->load->library('session');
		$this->load->helper('form');
		$this->load->helper('url');
		$this->load->library('form_validation');
		$this->smtp = $this->config->item('smtp');
		$this->load->library('email', $this->smtp);
		$this->email->set_newline("\r\n");
	}

	public function manage() {
		//管理员帐号在这里配置
		$admin_users_name = $this->config->item('admin_users_name');
		if ($user = $this->_is_login()) {
			if (in_array($user->name, $admin_users_name)) {
				//TODO:
				$this->db->order_by('score', "DESC");
				$this->db->order_by('finished', "DESC");
				$query = $this->db->get_where("user_game");
				$view_data = array(
					'total' => $query->num_rows(),
					'rows' => $query->result()
				);
				foreach ($view_data['rows'] as $key => $row) {
					$user = array_shift($this->db->get_where('user', array('uid' => $row->uid))->result());
					$view_data['rows'][$key]->user = $user;
					$shared_status = json_decode($row->shared_status, TRUE);
					if (empty($shared_status['shared_mail'])) {
						$shared_status['shared_mail'] = array();
					}
					if (empty($shared_status['shared_social'])) {
						$shared_status['shared_social'] = array();
					}
					$view_data['rows'][$key]->shared_status = $shared_status;
				}
				$this->load->view('game_manage', $view_data);
			}
			else {
				$this->load->view('access_deny');
			}
		}
		else {
			redirect('user/login_form');
		}
	}

	public function index() {
		redirect('user/minigame');
	}

	private function _is_login() {
		$user = $this->session->userdata('user');
		if ($user) {
			return $user;
		}
		else {
			return FALSE;
		}
	}

	// POST: social_brand, gid
	public function game_social_share() {
		$social_brand = $this->input->post('social_brand');
		$gid = $this->input->post('gid');
		$user = $this->_is_login();
		$allow_social = array('qq', 'sina', 'kaixin', 'renren');
		$data = array(
			'success' => 0,
			'message' => ''
		);
		if (in_array($social_brand, $allow_social)) {
			//更新分享统计
			$uid = $user->uid;
			$query = $this->db->get_where("user_game", array('uid' => $uid, 'gid' => $gid));
			if ($query->num_rows() >= 1) {
				$user_game = array_shift($query->result());
				if ($user_game->finished == 0) {
					$data['success'] = 0;
					$data['message'] = "您必须要完成游戏才能分享";
				}
				else {
					$shared_status = json_decode($user_game->shared_status, TRUE);
					if (!$shared_status) {
						$shared_status = array();
					}
					$shared_status['shared_social'][] = $social_brand;
					$this->db->update('user_game', array('shared_status' => json_encode($shared_status)), array('id' => $user_game->id));
					$data['success'] = 1;
				}
			}
			else {
				$data['success'] = 0;
				$data['message'] = "您必须要完成游戏才能分享";
			}
		}
		else {
			$data['message'] = "分享被拒绝";
		}
		$this->output->set_output(json_encode($data));
	}

	public function game_email_share() {
		$this->form_validation->set_rules('user_1', "Email Address", 'valid_mail');
		$this->form_validation->set_rules('user_2', "Email Address", 'valid_mail');
		$this->form_validation->set_rules('user_3', "Email Address", 'valid_mail');
		$this->form_validation->set_rules('user_4', "Email Address", 'valid_mail');

		$data = array(
			'message' => '',
			'success' => 0
		);
		if ($this->form_validation->run() === FALSE)  {
			$data['message'] = $this->form_validation->error_string();
			$this->output->set_output(json_encode($data));
		}
		else {
			$users_shared = array();
			if ($this->input->post('user_1')) {
				$users_shared[] = $this->input->post('user_1');
			}
			if ($this->input->post('user_2')) {
				$users_shared[] = $this->input->post('user_2');
			}
			if ($this->input->post('user_3')) {
				$users_shared[] = $this->input->post('user_3');
			}
			if ($this->input->post('user_4')) {
				$users_shared[] = $this->input->post('user_4');
			}
			$user = $this->_is_login();
			foreach ($users_shared as $user_shared) {
				$subject = "{$user->name} 在Minigame 玩成游戏";
				$body = "你也来玩!!";
				$this->email->from($this->smtp['smtp_user']);
				$this->email->to($users_shared);
				$this->email->subject($subject);
				$this->email->message($body);
				$this->email->send();
			}

			//更新分享统计
			$uid = $user->uid;
			$gid = $this->input->post('gid');
			$query = $this->db->get_where("user_game", array('uid' => $uid, 'gid' => $gid));
			if ($query->num_rows() >= 1) {
				$user_game = array_shift($query->result());
				if ($user_game->finished == 0) {
					$data['success'] = 0;
					$data['message'] = "您必须要完成游戏才能分享";
				}
				else {
					$shared_status = json_decode($user_game->shared_status, TRUE);
					if (!$shared_status) {
						$shared_status = array();
					}
					$shared_status['shared_mail'] = $users_shared;
					$this->db->update('user_game', array('shared_status' => json_encode($shared_status)), array('id' => $user_game->id));
					$data['success'] = 1;
				}
			}
			else {
				$data['success'] = 0;
				$data['message'] = "您必须要完成游戏才能分享";
			}

		}

		$this->output->set_output(json_encode($data));
	}
}