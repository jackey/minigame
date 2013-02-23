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
			$data['data'] = $game;

			//更新游戏访问时间
			update_game_access_time();
		}
		$this->output->set_output(json_encode($data));
	}
}