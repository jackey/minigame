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
	<?php echo form_open('/user', array('id' => 'newsletter_form'))?>
		<div>
			<?php echo form_label('Email:', 'email', array())?>
			<?php echo form_input('email')?>
		</div>
		<div>
			<?php echo form_button(array('name' => 'register_newsletter', 'type' => 'button', 'content' => 'Register Our Newsletter'))?>
		</div>
	<?php echo form_close()?>
	<?php echo validation_errors();?>
</div>

</body>
</html>