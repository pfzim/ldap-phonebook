<?php include(ABSPATH.'templ'.DIRECTORY_SEPARATOR.'tpl.header.php'); ?>
		<div class="login-block">
			<h1 align="center"><?php eh($lang["registerRegistrationForm"]); ?></h1>
			<form action="<?php eh("$self?action=reg"); ?>" method="post">
				<?php eh($lang["registerLogin"]); ?> <input name="login" type="text" /><br />
				<?php eh($lang["registerPassword"]); ?> <input name="passwd" type="password" /><br />
				<?php eh($lang["registerEMail"]); ?> <input name="mail" type="text" /><br />
				<?php if(!empty($error_msg)) { ?>
				<p><?php eh($error_msg); ?></p>
				<?php } ?>
				<input type="submit" value="<?php eh($lang["registerRegister"]); ?>" /><br />
			</form>
			<a href="<?php eh("$self?action=login"); ?>"><?php eh($lang["registerLoginHREF"]); ?></a>
		</div>
<?php include(ABSPATH.'templ'.DIRECTORY_SEPARATOR.'tpl.footer.php'); ?>
