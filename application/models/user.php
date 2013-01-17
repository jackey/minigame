<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class UserModel extends CI_Model {
	var $uid;
	var $name;
	var $phone;
	var $pass;
	var $mail;
	var $created;
	var $access;
	var $login;
	var $status;
	var $real_name;

	function __construct($uid = NULL){
		if ($nid || TRUE) {
			print_r($this->db);
		}
		parent::__construct();
	}

	public function register($model_user) {
		$created_time = time();
	}

	public function login($name, $password) {

	}
}