<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('load_user_game')) {
	function load_user_game($db, $user){
		$uid = $user->uid;
		$sql = "SELECT * FROM user_game LEFT JOIN game ON game.gid=user_game.gid WHERE user_game.uid = {$uid} AND user_game.finished = 0";

		$game = $db->query($sql)->result();
		return array_shift($game);
	}
}

if(!function_exists('helper_start_game')) {
	function helper_start_game($db, $user) {
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
						'map' => json_encode(helper_generate_game_map()),
					);
					$ret = $db->insert('user_game', $user_game);
					if ($ret) {
						return load_user_game($db, $user);
					}
				}
			}
		}
	}
}

if (!function_exists('helper_update_game')) {
	function helper_update_game($db, $key, $value) {
		$sql = "UPDATE game set access";
	}
}

if (!function_exists('helper_update_game_access_time')) {
	function helper_update_game_access_time($db) {
		$sql = "UPDATE game set access=".time();
		$db->query($sql);
	}
}

//辅助方法，一般不能直接被调用
if (!function_exists('helper_generate_game_map')) {
	function helper_generate_game_map() {
		$drips = array();
		for($i = 0; $i < 11; $i++) {
			$drips[] = array('pos' => rand(1, 20), 'status' => 0);
		}

		return $drips;
	}
}


if (!function_exists('helper_update_game_map')) {
	function helper_update_game_map($db, $gid, $map_id, $status = 1) {
		$sql = "SELECT * FROM user_game WHERE gid = {$gid}";
		$result = $db->query($sql)->result();
		$user_game = array_shift($result);
		if ($user_game) {
			$map = json_decode($user_game->map);
			foreach ($map as $m) {
				if ($m->pos == $map_id) {
					$m->status = 1;
				}
			}
			$map = json_encode($map);
			$data = array(
				'map' => $map
			);
			return $db->update('user_game', $data);
		}
	}
}


