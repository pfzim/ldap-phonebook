<?php include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.header.php'); ?>
			<div class="login-block">
				<h1><?php eh('Change password') ?></h1>
				<form id="passwd_change">
					<?php eh('Old password') ?>
					<input id="" name="old_passwd" type="password" autofocus="autofocus"/><br />
					<div id="old_passwd-error" class="form-error"></div>
					<?php eh('New password') ?>
					<input id="passwd" name="passwd" type="password" /><br />
					<div id="passwd-error" class="form-error"></div>
					<?php eh('Retype password') ?>
					<input id="passwd2" name="passwd2" type="password" /><br />
					<div id="passwd2-error" class="form-error"></div>
				</form>
				<div id="error-message" class="form-error"></div>
				<button class="button-accept" type="button" onclick="f_post_form('passwd_change');"><?php eh('Change password') ?></button>
			</div>
		</div>
<?php include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.footer.php'); ?>
