<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH.'/libraries/SaeTOAuthV2.php';

class User extends CI_Controller {

	var $max_game_element = 10;

	public function __construct() {
		parent::__construct();

		$this->load->library('session');
		$this->load->helper('form');
		$this->load->helper('url');
		$this->load->library('form_validation');

		$this->wb_akey = $this->config->item('wb_akey');
		$this->wb_skey = $this->config->item('wb_skey');
		$this->wb_callback_url = $this->config->item('wb_callback_url');
	}

	public function index()
	{
		$data = array();
		//1.判断是否登录
		if ($user = $this->_is_login())	{
			$data += array(
				'user' => (object)$user,
				'game' => (object)$this->new_game(),
				'max_game_element' => $this->max_game_element
			);
		}
		//如果是weibo 用户验证后的callback
		else if ($this->input->get('code')) {
			$wb_akey = $this->wb_akey;
			$wb_skey = $this->wb_skey;
			$wb_callback_url = $this->wb_callback_url;
			$tmp_user = NULL;

			$o = new SaeTOAuthV2($wb_akey , $wb_skey);
			$token = NULL;
			$keys = array();

			$state = $this->input->get('state');
			if ( empty($state) || $state !== $this->session->userdata('weibo_state')) {
				echo '非法请求！';
			}
			else {
				$this->session->unset_userdata('weibo_state');

				$keys['code'] = $this->input->get('code');
				$keys['redirect_uri'] = $wb_callback_url;
				try {
					$token = $o->getAccessToken( 'code', $keys );
				} catch (OAuthException $e) {
					print_r($e);
				}
			}

			if ($token) {
				$this->session->set_userdata('token', $token);
				setcookie( 'weibojs_'.$o->client_id, http_build_query($token));
				$weibo_user = $this->weibo_account();
				$screen_name = $weibo_user['screen_name'];
				$query = $this->db->get_where('user', array('weibo_screen_name' => $screen_name));
				if($query->num_rows()) {
					$user = array_shift($query->result());
					$tmp_user = $user;
					$this->session->set_userdata('user', $user);
				}
				else {
					//如果客户之前没有用weibo账户登录过 
					//我们则先创建一个系统账户，再跳转到注册页面，并且赋一个默认的值.
					$new_user = array(
						'name' => $weibo_user['screen_name'],
						'phone' => '',
						'pass' => md5(''),
						'mail' => '',
						'created' => time(),
						'access' => time(),
						'login' => time(),
						'status' => 1,
						'real_name' => $weibo_user['screen_name'],
						'delivery_address' => '',
						'weibo_screen_name' => $weibo_user['screen_name'],
					);
					$this->db->insert('user', $new_user);
					$uid = $this->db->insert_id();
					$new_user['uid'] = $uid;
					$tmp_user = $new_user;
					$this->session->set_userdata('user', $new_user);
				}
				//如果必填字段都存在，则说明已经注册完成了
				if ($user->name && $user->phone && $user->pass && $user->mail && $user->delivery_address) {
					$data += array(
						'weibo_user_profile_is_updated' => 1
					);
				}
				else {
					$data += array(
						'weibo_user_profile_is_updated' => 0
					);
				}
			}
		}
		$this->load->view('index', $data);
	}

	private function new_game() {
		// 开始游戏前 先在数据库生成一个游戏记录
		$new_game = array(
			'name' => uniqid(),
			'uuid' => uniqid(),
			'created' => time(),
			'access' => time(),
		);
		$this->db->insert('game', $new_game);
		$gid = $this->db->insert_id();
		$new_game['gid'] = $gid;

		return $new_game;
	}

	public function register() {
		if ($this->_is_login()) {
			redirect('user');
		}
		else {
			$this->load->view('user_register_page');
		}
	}

	public function login_process() {
		$this->form_validation->set_rules('pass', "Your Password", 'required|min_length[6]');
		$this->form_validation->set_rules('name', "User name", 'required|min_length[5]');

		if ($this->form_validation->run() === FALSE) {
			$data = array(
				'success' => 0,
				'message' => $this->form_validation->error_string(),
			);
			$this->output->set_output(json_encode($data));
		}
		else {
			$authcode = $this->input->post('authcode');
			if ($authcode != $this->session->userdata('authcode')) {
				$data = array(
					'success' => 0,
					'message' => '验证码错误'
				);
				$this->output->set_output(json_encode($data));
			}
			else {
				$query = $this->db->get_where('user', array('name' => $this->input->post('name')));
				$user = array_shift($query->result());
				if ($user && $user->pass == md5($this->input->post('pass'))) {
					// Store into session.
					$this->init_user_session($user);
					$data = array(
						'success' => 1,
						'message' => '登录成功'
					);
					$this->output->set_output(json_encode($data));
				}
				else {
					$data = array(
						'success' => 0,
						'message' => '用户不存在或者密码错误'
					);
					$this->output->set_output(json_encode($data));
				}
			}
		}
	}

	public function register_process() {
		//表单验证规则
		$this->form_validation->set_error_delimiters("<div class='error'></div>");
		$this->form_validation->set_rules('name', "User name", 'required|min_length[5]');
		$this->form_validation->set_rules('mail', "Email Address", 'required|valid_mail');
		$this->form_validation->set_rules('phone', "User Phone", 'required|min_length[5]');
		$this->form_validation->set_rules('pass', "Your Password", 'required|min_length[6]');
		$this->form_validation->set_rules('delivery_address', "Your Delivery Address", 'required');
		$this->form_validation->set_rules('passconf', "Your Password Confirm", 'required|matches[pass]');

		// $this->output->set_content_type('application/json');
		// $this->output->set_header('Cache-Control: no-cache, must-revalidate');
		//验证失败后 继续提示注册
		if ($this->form_validation->run() === FALSE) {
			$data = array(
				'success' => 0,
				'message' => $this->form_validation->error_string(),
			);
			$this->output->set_output(json_encode($data));
		}
		//否则进入到游戏界面
		else {
			$authcode = $this->input->post('authcode');
			if ($authcode != $this->session->userdata('authcode')) {
				$data = array(
					'success' => 0,
					'message' => '验证码错误'
				);
				$this->output->set_output(json_encode($data));
			}
			else {
				//进入之前，先返回前端，然后保存提交的新用户
				$this->load->model('user');
				$query_name = $this->db->get_where('user', array('name' => $this->input->post('name')));
				$query_mail = $this->db->get_where('user', array('mail' => $this->input->post('mail')));
				
				if ($query_name->num_rows() > 0) {
					$data = array(
						'success' => 0,
						'message' => '用户名被占用',
					);
					$this->output->set_output(json_encode($data));
				}
				else if ($query_mail->num_rows() > 0) {
					$data = array(
						'success' => 0,
						'message' => '邮件被占用',
					);
					$this->output->set_output(json_encode($data));
				}
				else {
					$user = array(
						'name' => $this->input->post('name'),
						'phone' => $this->input->post('phone'),
						'pass' => md5($this->input->post('pass')),
						'mail' => $this->input->post('mail'),
						'created' => time(),
						'access' => time(),
						'login' => time(),
						'status' => 1,
						'real_name' => $this->input->post('real_name'),
						'delivery_address' => $this->input->post('delivery_address'),
					);
					$this->db->insert('user', $user);

					$uid = $this->db->insert_id();
					$user['uid'] = $uid;

					$data = array(
						'success' => 1,
						'message' => ''
					);
					//把当前注册的用户加入session 实现自动登录
					$this->session->set_userdata('user', (object)$user);
					$this->output->set_output(json_encode($data));
				}


			}
		}
	}

	public function login_form() {
		if ($this->_is_login()) {
			redirect('user');
		}
		else {
			$wb_akey = $this->config->item('wb_akey');
			$wb_skey = $this->config->item('wb_skey');
			$wb_callback_url = $this->config->item('wb_callback_url');

			$o = new SaeTOAuthV2($wb_akey , $wb_skey);
			$state = uniqid( 'weibo_', true);
			$this->session->set_userdata('weibo_state', $state);
			$code_url = $o->getAuthorizeURL($wb_callback_url , 'code', $state);

			$this->load->view('user_login_page', array('code_url' => $code_url));
		}
	}

	public function minigame() {
		if ($this->_is_login()) {
			// 开始游戏前 先在数据库生成一个游戏记录
			$new_game = $this->new_game();
			$user = $this->_is_login();
			$query = $this->db->get_where("user_game", array('uid' => $user->uid));
			$has_played = 0;
			if ($query->num_rows() >= 1) {
				$has_played = 1;
			}
			$this->load->view('tenyears/minigame_page', array('user' => $user, 
				'game' => (object)$new_game, 
				'max_game_element' => $this->max_game_element,
				'has_played' => $has_played)
			);
		}
		else {
			redirect('user');
		}
	}

	public function minigame_process() {
		$gid = $this->input->post('gid');
		$uid = $this->input->post('uid');
		$user = $this->_is_login();
		$res_data = array(
			'success' => 0,
			'message' => ''
		);
		if ($user && $user->uid == $uid) {
			$query_game = $this->db->get_where("game", array('gid' => $gid))->result();
			$game = array_shift($query_game);

			$query_user_game = $this->db->get_where("user_game", array('gid' => $gid, 'uid' => $uid))->result();
			if (empty($query_user_game)) {
				$new_user_game = array(
					'gid' => $gid,
					'uid' => $uid,
					'started' => time(),
					'score' => 1,
					'finished' => 0,
				);
				$this->db->insert('user_game', $new_user_game);
				$id = $this->db->insert_id();
				$res_data['success'] = 1;
			}
			else {
				$user_game = array_shift($query_user_game);
				$user_game->score += 1;
				$updated_data = new stdClass;
				$updated_data->score = $user_game->score; 
				//游戏已经完成
				if ($updated_data->score == $this->max_game_element) {
					$updated_data->finished = time();
					$res_data['success'] = 1;
					$res_data['message'] = "游戏过关";
				}
				//更新用户找到的图片数目
				if ($updated_data->score <= $this->max_game_element) {
					$this->db->set($updated_data);
					$this->db->where('id', $user_game->id);
					$this->db->update('user_game');

					$res_data['success'] = 1;
				}

			}
		}
		else {
			$res_data['message'] = '非法用户';
		}

		$this->output->set_output(json_encode($res_data));
	}

	public function user_game_is_finished() {
		$uid = $this->input->post('uid');
		$gid = $this->input->post('gid');

		$query = $this->db->get_where('user_game', array('uid' => $uid, 'gid' => $gid))->result();
		$user_game = array_shift($query);

		if ($user_game && $user_game->finished != 0) {
			$this->output->set_output(json_encode(array('success' => 1, 'message' => '')));
		}
		else {
			$this->output->set_output(json_encode(array('success' => 0, 'message' => '')));
		}
	}

	public function logout() {
		$this->session->unset_userdata('user');
		$this->session->sess_destroy();
		redirect('user');
	}

	public function authcode() {
	    $num="";
	    for($i=0;$i<4;$i++){
	    	$num .= rand(0,9);
	    }
	   //4位验证码也可以用rand(1000,9999)直接生成
	   //将生成的验证码写入session，备验证页面使用
	    $this->session->set_userdata("authcode", $num);
	   //创建图片，定义颜色值
	    Header("Content-type: image/PNG");
	    srand((double)microtime()*1000000);
	    $im = imagecreate(60,20);
	    $black = ImageColorAllocate($im, 0,0,0);
	    $gray = ImageColorAllocate($im, 200,200,200);
	    imagefill($im,0,0,$gray);

	    //随机绘制两条虚线，起干扰作用
	    $style = array($black, $black, $black, $black, $black, $gray, $gray, $gray, $gray, $gray);
	    imagesetstyle($im, $style);
	    $y1=rand(0,20);
	    $y2=rand(0,20);
	    $y3=rand(0,20);
	    $y4=rand(0,20);
	    imageline($im, 0, $y1, 60, $y3, IMG_COLOR_STYLED);
	    imageline($im, 0, $y2, 60, $y4, IMG_COLOR_STYLED);

	    //在画布上随机生成大量黑点，起干扰作用;
	    for($i=0;$i<80;$i++)
	    {
	   		imagesetpixel($im, rand(0,60), rand(0,20), $black);
	    }
	    //将四个数字随机显示在画布上,字符的水平间距和位置都按一定波动范围随机生成
	    $strx=rand(3,8);
	    for($i=0;$i<4;$i++){
		    $strpos=rand(1,6);
		    imagestring($im,5,$strx,$strpos, substr($num,$i,1), $black);
		    $strx+=rand(8,12);
	    }
	    ImagePNG($im);
	    ImageDestroy($im);
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

	private function init_user_session($user) {
		unset($user->delivery_address);
		$this->session->set_userdata(array('user' => $user));
	}

	public function weibo_login() {
		if ($this->_is_login()) {
			redirect('user');
			return;
		}
		$wb_akey = $this->config->item('wb_akey');
		$wb_skey = $this->config->item('wb_skey');
		$wb_callback_url = $this->config->item('wb_callback_url');

		$o = new SaeTOAuthV2($wb_akey , $wb_skey);
		$state = uniqid( 'weibo_', true);	
		$this->session->set_userdata('weibo_state', $state);
		$code_url = $o->getAuthorizeURL($wb_callback_url , 'code', $state);

		$this->load->view('user_weibo_login_page', array('code_url' => $code_url));
	}

	public function get_auth_token() {
		$wb_akey = $this->wb_akey;
		$wb_skey = $this->wb_skey;
		$wb_callback_url = $this->wb_callback_url;
		$data = array(
			'success' => 0,
			'message' => '',
			'data' => array()
		);

		$o = new SaeTOAuthV2($wb_akey , $wb_skey);
		$token = NULL;
		if ($this->input->get('code')) {
			$keys['code'] = $this->input->get('code');
			$keys['redirect_uri'] = $wb_callback_url;
			try {
				$token = $o->getAccessToken('code', $keys);
				$data['success'] = 1;
				$data['data'] = array('token' => $token);

				$this->set_userdata('token', $token);

				$this->output->set_output(json_encode($data));
			} catch (OAuthException $e) {
				$data['message'] = $e->getMessage();
			}
		}
	}

	public function get_logined_weibo_user() {
		$token = $this->input->post('access_token');
		$uid = $this->input->post('weibo_uid');
		$wb_akey = $this->wb_akey;
		$wb_skey = $this->wb_skey;

		$weibo_client = new SaeTClientV2($wb_akey, $wb_skey , $access_token);
		$this->output->set_output(json_encode($weibo_client->show_user_by_id($uid)));
	}

	public function weibo_user_profile_is_updated() {
		$weibo_screen_name = $this->input->post('weibo_screen_name');
		$query = $this->db->get_where('user', array('weibo_screen_name' => $weibo_screen_name));
		$data = array(
			'success' => 0,
			'message' => ''
		);
		if($query->num_rows()) {
			$data['success'] = 1;
		}
		else {
			$data['success'] = 0;
		}

		//如果success == 1 说明用户已经存在数据库 但是可能没有更新数据
		if ($data['success'] == 1) {
			$user = array_shift($query->result);
			//如果必填字段都存在，则说明已经注册完成了
			if ($user->name && $user->phone && $user->pass && $user->mail && $user->delivery_address) {
				// TODO:
			}
			else {
				$data['success'] = 0;
			}
		}
		$this->output->set_output(json_decode($data));
	}

	public function weibo_user_is_registered() {
		$weibo_screen_name = $this->input->post('weibo_screen_name');
		$query = $this->db->get_where('user', array('weibo_screen_name' => $weibo_screen_name));
		$data = array(
			'success' => 0,
			'message' => ''
		);
		if($query->num_rows()) {
			$data['success'] = 1;
		}
		else {
			$data['success'] = 0;
		}

		$this->output->set_output(json_decode($data));
	}

	public function weibo_callback() {
		$wb_akey = $this->wb_akey;
		$wb_skey = $this->wb_skey;
		$wb_callback_url = $this->wb_callback_url;

		$o = new SaeTOAuthV2($wb_akey , $wb_skey);
		$token = NULL;
		if ($this->input->get('code')) {
			$keys = array();

			$state = $this->input->get('state');
			if ( empty($state) || $state !== $this->session->userdata('weibo_state')) {
				echo '非法请求！';
			}
			else {
				$this->session->unset_userdata('weibo_state');

				$keys['code'] = $this->input->get('code');
				$keys['redirect_uri'] = $wb_callback_url;
				try {
					$token = $o->getAccessToken( 'code', $keys );
				} catch (OAuthException $e) {
					print_r($e);
				}
			}
		}
		else {
			//TODO:
			print("Error when get weibo auth code");
		}

		if ($token) {
			$this->session->set_userdata('token', $token);
			setcookie( 'weibojs_'.$o->client_id, http_build_query($token));
			$weibo_user = $this->weibo_account();
			$screen_name = $weibo_user['screen_name'];
			$query = $this->db->get_where('user', array('weibo_screen_name' => $screen_name));
			if($query->num_rows()) {
				$user = array_shift($query->result());
				$this->session->set_userdata('user', $user);
				redirect('user');
			}
			else {
				//如果客户之前没有用weibo账户登录过 
				//我们则先创建一个系统账户，再跳转到注册页面，并且赋一个默认的值.
				$new_user = array(
					'name' => $weibo_user['screen_name'],
					'phone' => '',
					'pass' => md5(''),
					'mail' => '',
					'created' => time(),
					'access' => time(),
					'login' => time(),
					'status' => 1,
					'real_name' => $weibo_user['screen_name'],
					'delivery_address' => '',
					'weibo_screen_name' => $weibo_user['screen_name'],
				);
				$this->db->insert('user', $new_user);

				$uid = $this->db->insert_id();
				$new_user['uid'] = $uid;

				$this->session->set_userdata('user', $new_user);

				//用户第一次微薄登录后 调转到用户详情编辑页面
				redirect('user/profile_update');
			}
		}
	}

	private function weibo_account() {
		$wb_akey = $this->wb_akey;
		$wb_skey = $this->wb_skey;
		$wb_callback_url = $this->wb_callback_url;
		$token = $this->session->userdata('token');

		$weibo_client = new SaeTClientV2($wb_akey, $wb_skey , $token['access_token']);
		return $weibo_client->show_user_by_id($token['uid']);
	}

	public function profile_update() {
		if (!$user = $this->_is_login()) {
			redirect('user/login_form');
		}
		else {
			$data = array('user' => (Object)$user);
			$this->load->view('user_register_page', $data);
		}
	}

	public function weibo_create_friend() {
		$our_weibo_uid = "2890812010";

		// Instance weibo client.
		$wb_akey = $this->wb_akey;
		$wb_skey = $this->wb_skey;
		$token = $this->session->userdata('token');
		$weibo_client = new SaeTClientV2($wb_akey, $wb_skey , $token['access_token']);

		return $weibo_client->follow_by_id($our_weibo_uid);
	}



	public function profile_update_process() {
		//表单验证规则
		$this->form_validation->set_error_delimiters("<div class='error'></div>");
		$this->form_validation->set_rules('name', "User name", 'required|min_length[5]');
		$this->form_validation->set_rules('mail', "Email Address", 'required|valid_mail');
		$this->form_validation->set_rules('phone', "User Phone", 'required|min_length[5]');
		$this->form_validation->set_rules('pass', "Your Password", 'required|min_length[6]');
		$this->form_validation->set_rules('delivery_address', "Your Delivery Address", 'required');
		$this->form_validation->set_rules('passconf', "Your Password Confirm", 'required|matches[pass]');
		$create_friends = $this->input->post('create_friends');
		$receive_newsletter = $this->input->post('receive_newsletter');

		//如果客户允许关注weibo
		if ($create_friends == 'accept') {
			$ret = $this->weibo_create_friend();
		}

		// $this->output->set_content_type('application/json');
		// $this->output->set_header('Cache-Control: no-cache, must-revalidate');
		//验证失败后 继续提示注册
		if ($this->form_validation->run() === FALSE) {
			$data = array(
				'success' => 0,
				'message' => $this->form_validation->error_string(),
			);
			$this->output->set_output(json_encode($data));
		}
		//否则进入到游戏界面
		else {
			$user = $this->_is_login();
			$data = array(
				'success' => 0,
				'message' => ''
			);
			if (!$user) {
				$data['message'] = "用户非法";
			}
			else {
				$updated_data = array(
					'name' => $this->input->post('name'),
					'phone' => $this->input->post('phone'),
					'pass' => md5($this->input->post('pass')),
					'mail' => $this->input->post('mail'),
					'created' => time(),
					'access' => time(),
					'login' => time(),
					'status' => 1,
					'real_name' => $this->input->post('real_name'),
					'delivery_address' => $this->input->post('delivery_address'),
					'receive_newsletter' => $receive_newsletter
				);
				$this->db->update('user', $updated_data, array('uid' => $this->input->post('uid')));
				$data['success'] = 1;
			}
			$this->output->set_output(json_encode($data));
		}
	}
}