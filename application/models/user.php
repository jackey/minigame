<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends CI_Model {
	var $uid;
	var $name;
	var $email;
	var $phone;
	var $real_name;
	var $created_time;
	var $updated_time;

	function __construct(){
		parent::__construct();
	}

	public function register($model_user) {
		$created_time = time();
	}

	public function login($name, $password) {

	}	
}