<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Welcome to CodeIgniter</title>
</head>
<body>

<div id="container">
	<?php echo form_open('/user/login')?>
		<div>
			<?php echo form_label('Login:', 'username', array())?>
			<?php echo form_input('username')?>
		</div>
		<div>
			<?php echo form_label('Password:', 'password', array())?>
			<?php echo form_password('password')?>
		</div>
		<div>
			<?php echo form_button(array('name' => 'login', 'type' => 'submit', 'content' => 'Login'))?>
		</div>
	<?php echo form_close()?>
	<?php echo validation_errors();?>
</div>

</body>
</html>