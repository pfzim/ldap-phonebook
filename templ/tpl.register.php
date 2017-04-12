<?php include("tpl.header.php"); ?>
		<div class="login-block">
			<h1 align="center">Registration form</h1>
			<form action="<?php eh("$self?action=reg"); ?>" method="post">
				Login: <input name="login" type="text" /><br />
				Password: <input name="passwd" type="password" /><br />
				E-Mail: <input name="mail" type="text" /><br />
				<?php if(!empty($error_msg)) { ?>
				<p><?php eh($error_msg); ?></p>
				<?php } ?>
				<input type="submit" value="Register" /><br />
			</form>
			<a href="<?php eh("$self"); ?>">Login</a>
		</div>
<?php include("tpl.footer.php"); ?>
