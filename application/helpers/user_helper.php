<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('current_user')) {
	function current_user($session) {
		$user = $session->userdata('user');
		if ($user) {
			return $user;
		}
		else {
			return FALSE;
		}
	}
}

if (!function_exists('is_login')) {
	function is_login($session) {
		$user = $session->userdata('user');
		if ($user) {
			return $user;
		}
		else {
			return FALSE;
		}
	}
}

if (!function_exists('logout')) {
	function logout($session){
		$session->unset_userdata('user');
		$session->sess_destroy();
	}
}