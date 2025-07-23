<?php include(TEMPLATES_DIR.'tpl.header.php'); ?>
			<div class="login-block">
				<h1><?php L('LoginHdr') ?></h1>
				<form action="<?php ln('logon'); ?>" method="post">
					<input name="return" type="hidden" value="<?php eh($return_url); ?>"/><br />
					<?php L('UserName') ?>
					<input name="login" type="text" autofocus="autofocus" placeholder="domain\user_name"/><br />
					<?php L('Password') ?>
					<input name="passwd" type="password" /><br />
					<?php if(!empty($error_msg)) { ?>
					<p><?php eh($error_msg); ?></p>
					<?php } ?>
					<button type="submit"><?php L('LoginBtn') ?></button><br />
					<br />
				</form>
				<a href="<?php ln('register_form') ?>" onclick="return f_show_form(this.href);"><?php L('Register') ?></a> &VerticalSeparator; <a href="<?php ln('password_reset_send_form') ?>" onclick="return f_show_form(this.href);"><?php L('ResetPasswordBtn') ?></a><br />
			</div>
<?php include(TEMPLATES_DIR.'tpl.universal-form.php'); ?>
<?php include(TEMPLATES_DIR.'tpl.footer.php'); ?>
