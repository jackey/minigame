<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Welcome to Mini Game</title>
	<script type="text/javascript" src="<?php echo '/application/public/js/jquery.js'?>"></script>
	<script type="text/javascript" src="<?php echo '/application/public/js/jquery.form.js'?>"></script>
	<script type="text/javascript" src="<?php echo '/application/public/js/minigame.js'?>"></script>
</head>
<body>

<div id="container">
	<?php echo form_open('/user/login_process', array('id' => 'user_login_form'))?>
		<div>
			<?php echo form_label('Login:', 'name', array())?>
			<?php echo form_input('name')?>
		</div>
		<div>
			<?php echo form_label('Password:', 'pass', array())?>
			<?php echo form_password('pass')?>
		</div>
		<div>
			<a href=""><img src="<?php echo site_url().'/user/authcode'?>" /></a>
		</div>
		<div>
			<?php echo form_button(array('name' => 'login', 'type' => 'submit', 'content' => 'Login'))?>
		</div>
	<?php echo form_close()?>
	<?php echo validation_errors();?>

	<div>
		<a href="<?php echo site_url().'/user/register'?>">注册</a>
	</div>
</div>

</body>
</html>