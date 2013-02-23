<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Game extends CI_Controller {
	public function __construct() {
		parent::__construct();

		$this->load->library('session');
		$this->load->helper('form');
		$this->load->helper('url');
		$this->load->library('form_validation');

		// Custom library
		$this->load->helper("user");
		$this->load->helper('game');
	}

	public function index() {
		$this->load->view('welcome_message');
	}

	public function start_game() {
		$data = array(
			'success' => 0,
			'message' => '',
			'data' => array(),
		);
		//如果用户没有登陆  是不能玩游戏的
		if (is_login($this->session)) {
			$user = current_user($this->session);
			$game = load_user_game($this->db, $user);
			if (!$game) {
				$game = helper_start_game($this->db, $user);
			}
			$data['data'] = $game;
			$data['success'] = 1;

			//更新游戏访问时间
			helper_update_game_access_time($this->db, $game->gid);
		}
		$this->output->set_output(json_encode($data));
	}

	// GET:
	// {gid => 1, map_id => 1}
	public function find_one_map() {
		$data = array(
			'success' => 0,
			'message' => '',
			'data' => array(),
		);
		if (is_login($this->session)) {
			$user = current_user($this->session);
			$gid = $this->input->get('gid');
			$map_id = $this->input->get('map_id');
			helper_update_game_map($this->db, $gid, $map_id);
			$data['success'] = 1;

			//如果游戏已经完成，则我们更改游戏完成的状态
			if (helper_game_is_finished($this->db, $gid)) {
				//我们就修改finish状态
				helper_update_game_finish_status($this->db, $gid);
			}
		}
		return $this->output->set_output(json_encode($data));
	}

	//GET:
	// {gid: 1}
	public function game_is_finished() {
		$gid = $this->input->get('gid');
		$finished = helper_game_is_finished($this->db, $gid);
		$data = array(
			'success' => 0,
			'message' => '',
			'data' => array(),
		);
		if ($finished) {
			$data['success'] = 1;
			$data['data']['finished'] = 1;
		}
		else {
			$data['data']['finished'] = 0;
		}
		return $this->output->set_output(json_encode($data));
	}

	// POST: social_brand, gid
	public function game_social_share() {
		$social_brand = $this->input->post('social_brand');
		$gid = $this->input->post('gid');
		$user = current_user();
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