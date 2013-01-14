<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class UserGame extends CI_Model {
	var $ugid;
	var $play_time;
	var $finished_time;
	var $score;

	function __construct(){
		parent::__construct();
	}
}