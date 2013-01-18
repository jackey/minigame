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
	<?php if ($total == 0):?>
		当前还没有人玩游戏
	<?php else:?>
		当前玩家总数：<?php echo $total?>
		<table>
			<theader>
				<th>序列号</td>
				<th>用户名</td>	
				<th>游戏开始时间</td>
				<th>游戏完成时间</td>
				<td>得分</td>		
				<td>分享状态</td>
			</theader>
			<tbody>
				<?php foreach ($rows as $row):?>
					<tr>
						<td><?php echo $row->id?></td>
						<td><?php echo $row->user->name?></td>
						<td><?php echo $row->started?></td>
						<td><?php echo $row->finished?></td>
						<td><?php echo $row->score?>分</td>
						<td>
							<div>
								<h4>邮件分享:</h4>
								<?php foreach ($row->shared_status['shared_mail'] as $mail):?>
									<span class="margin-2"><?php echo $mail?></span>
								<?php endforeach;?>
							</div>
							<div>
								<h4>社区分享:</h4>
								<?php foreach ($row->shared_status['shared_social'] as $social):?>
									<span class="margin-2"><?php echo $social?></span>
								<?php endforeach;?>
							</div>
						</td>
					</tr>
				<?php endforeach;?>
			</tbody>
		</table>
	<?php endif;?>
</div>
</body>
</html>