<?php include("tpl.header.php"); ?>
			<div class="login-block">
				<h1>Login</h1>
				<form action="<?php eh("$self?action=logon"); ?>" method="post">
					Login:
					<input name="login" type="text" autofocus="autofocus"/><br />
					Password:
					<input name="passwd" type="password" /><br />
					<?php if(!empty($error_msg)) { ?>
					<p><?php eh($error_msg); ?></p>
					<?php } ?>
					<input type="submit" value="Login" /><br />
				</form>
				<a href="<?php eh("$self?action=register"); ?>">Register</a>
			</div>
		</div>
<?php include("tpl.footer.php"); ?>
