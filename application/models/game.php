<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Game extends CI_Model {
	var $gid;
	var $game_name = '';
	var $created_time;
	var $updated_time;

	function __construct(){
		parent::__construct();
	}

	public function new_game() {
		$created_time = time();
	}
}