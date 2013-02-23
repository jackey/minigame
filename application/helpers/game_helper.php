<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('load_user_game')) {
	function load_user_game($db, $user){
		$uid = $user->uid;
		$sql = "SELECT * FROM user_game LEFT JOIN game ON game.gid=user_game.gid WHERE user_game.uid = {$uid} AND user_game.finished = 0";

		$game = $db->query($sql)->result();
		return array_shift($game);
	}
}

if(!function_exists('new_game')) {
	function start_game($db, $user) {
		//1. 首先判断用户是否登陆 如果没有登陆再不能玩游戏
		if ($user) {
			//2. 首先得到当前用户正在玩或者没有完成的游戏
			$game = load_user_game($db, $user);
			//3. 如果用户玩了游戏，则直接返回之前的游戏
			if ($game) {
				return $game;
			}
			//4. 否则开始一个全新的游戏
			else {
				$new_game = array(
					'name' => time(),
					'uuid' => uniqid(),
					'created' => time(),
					'access' => time(),
				);
				$ret = $db->insert('game', $new_game);
				if ($ret) {
					$new_game['gid'] = $db->insert_id();
					$user_game = array(
						'gid' => $new_game['gid'],
						'uid' => $user->uid,
						'started' => $new_game['created'],
						'finished' => 0,
						'score' => 0,// 完成了几个水滴
						'shared_status' => '',
					);
					$ret = $db->insert('user_game', $user_game);
				}
			}
		}
	}
}

if (!function_exists('update_game')) {
	function update_game($db, $key, $value) {
		$sql = "UPDATE game set access";
	}
}

if (!function_exists('update_game_access_time')) {
	function update_game_access_time($db) {
		$sql = "UPDATE game set access=".time();
		$db->query($sql);
	}
}


