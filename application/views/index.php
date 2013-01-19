<!DOCTYPE html>
<html lang="en">
<head>
	<base href="<?=base_url();?>">
	<meta charset="utf-8">
	<title>Welcome to Mini Game</title>
	<script type="text/javascript" src="<?php echo '/application/public/js/jquery.js'?>"></script>
	<script type="text/javascript" src="<?php echo '/application/public/js/jquery.form.js'?>"></script>
	<script type="text/javascript" src="<?php echo '/application/public/js/minigame.js'?>"></script>
	<link rel="stylesheet" href="<?php echo '/application/public/css/style.css'?>">
</head>
<body>

<div id="container">
	<div id="minigame_section_container"></div>
	<?php if (isset($user)):?>
		<?php echo form_open('/user/minigame_process', array('id' => 'user_submit_game_form'))?>
			<?php echo form_hidden('uid', $user->uid)?>
			<?php echo form_hidden('gid', $game->gid)?>
			<?php echo form_hidden('max_game_element', $max_game_element)?>
		<?php echo form_close()?>
		
		<div id="game_share_form_container">
			<div>
				<a href="javascript:void(0)" id="social_brand">QQ</a>
				<a href="sina">Sina</a>
				<a href="kaixin">Kaixin</a>
				<a href="renren">Renren</a>
			</div>
			<?php echo form_open('/game/game_email_share', array('id' => 'user_game_share_result', 'style' => 'display:block'))?>
				<div>
					<?php echo form_label('Email User 1:', 'user_1', array())?>
					<?php echo form_input('user_1')?>
				</div>
				<div>
					<?php echo form_label('Email User 2:', 'user_2', array())?>
					<?php echo form_input('user_2')?>
				</div>
				<div>
					<?php echo form_label('Email User 3:', 'user_3', array())?>
					<?php echo form_input('user_3')?>
				</div>
				<div>
					<?php echo form_label('Email User 4:', 'user_4', array())?>
					<?php echo form_input('user_4')?>
				</div>
				<div>
					<?php echo form_hidden('gid', $game->gid)?>
				</div>
				<div>
					<?php echo form_button("share", 'Share', array('id' => 'user_game_share_result_submit'))?>
				</div>
			<?php echo form_close()?>
		</div>
	<?php endif;?>
</div>
</body>
</html>