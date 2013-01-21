<!DOCTYPE html>
<html lang="en">
<head>
	<base href="<?=base_url();?>">
	<meta charset="utf-8">
	<title>Welcome to Mini Game</title>
	<script type="text/javascript" src="<?php echo '/application/public/js/jquery.js'?>"></script>
	<script type="text/javascript" src="<?php echo '/application/public/js/jquery.form.js'?>"></script>
	<script type="text/javascript" src="<?php echo '/application/public/js/minigame.js'?>"></script>
</head>
<body>

<div id="container">
	<?php if (!@$user->uid):?>
		<?php echo form_open('/user/register_process', array('id' => 'user_register_form'))?>
	<?php else: ?>
		<?php echo form_open('/user/profile_update_process', array('id' => 'user_register_form'))?>
	<?php endif;?>
		<div>
			<?php echo form_label('Login:', 'name', array())?>
			<?php echo form_input('name')?>
		</div>
		<div>
			<?php echo form_label('Email', 'mail', array())?>
			<?php echo form_input('mail')?>
		</div>
		<div>
			<?php echo form_label('Phone', 'phone', array())?>
			<?php echo form_input('phone')?>
		</div>
		<div>
			<?php echo form_label('Real Name', 'real_name', array())?>
			<?php echo form_input('real_name')?>
		</div>
		<div>
			<?php echo form_label('Delivery Address', 'delivery_address', array())?>
			<?php echo form_input('delivery_address')?>
		</div>
		<div>
			<?php echo form_label('Password:', 'pass', array())?>
			<?php echo form_password('pass')?>
		</div>
		<div>
			<?php echo form_label('Password Confirm:', 'passconf', array())?>
			<?php echo form_password('passconf')?>
		</div>
		<div>
			<?php echo form_hidden('uid', @$user->uid)?>
		</div>
		<div>
			<?php echo form_label('关注我们微薄', 'create_friends')?>
			<?php echo form_checkbox('create_friends', 'accept', TRUE);?>
		</div>
		<div>
		<?php if (@$user->uid):?>
			<?php echo form_button(array('name' => 'login', 'type' => 'submit', 'content' => 'Update Now!'))?>
		<?php else: ?>
			<div>
				<?php echo form_input('authcode')?>
				<img class="fresh_authcode" src="<?php echo site_url().'/user/authcode?'.time()?>" />
			</div>
			<?php echo form_button(array('name' => 'login', 'type' => 'submit', 'content' => 'Register Now!'))?>
		<?php endif;?>
		</div>
	<?php echo form_close()?>
	<?php echo validation_errors();?>
	<div>
		<a href="<?php echo site_url().'/user/register'?>">注册</a>
	</div>
	<div>
		<a href="<?php echo site_url().'/user/login_form'?>">登录</a>
	</div>
</div>

</body>
</html>