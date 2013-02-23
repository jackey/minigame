<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('helper_register_newsletter')) {
	function helper_register_newsletter($db, $email) {
		$data = array(
			'email' => $email,
			'created' => time()
		);
		$ret = $db->insert('newsletter', $data);
		if ($ret) {
			return TRUE;
		}
		return FALSE;
	}
}

if (!function_exists('helper_load_newsletter')) {
	function  helper_load_newsletter($db, $email) {
		$sql = "SELECT * FROM newsletter WHERE email='$email'";
		$query = $db->query($sql)->result();
		return array_shift($query);
	}
}