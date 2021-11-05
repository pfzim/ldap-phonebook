<?php include(TEMPLATES_DIR.'tpl.header.php'); ?>
			<div class="login-block">
				<h1><?php L('ApproveNewUser') ?></h1>
				<?php L('Login') ?>: <?php eh(@$user_info['login']) ?><br />
				<?php L('Mail') ?>: <?php eh(@$user_info['mail']) ?><br />
				<br />
				<form action="<?php ln('register_approve') ?>" method="post">
					<input type="hidden" name="uid" value="<?php eh($user_id); ?>"/>
					<input type="submit" value="<?php L('Approve') ?>" /><br />
				</form>
			</div>
<?php include(TEMPLATES_DIR.'tpl.footer.php'); ?>
