<?php include(ABSPATH.'templ'.DIRECTORY_SEPARATOR.'tpl.header.php'); ?>
			<div class="login-block">
				<h1><?php eh($lang["loginLoginHead"]) ?></h1>
				<form action="<?php eh("$self?action=logon"); ?>" method="post">
					<?php eh($lang["loginLogin"]) ?>
					<input name="login" type="text" autofocus="autofocus"/><br />
					<?php eh($lang["loginPassword"]) ?>
					<input name="passwd" type="password" /><br />
					<?php if(!empty($error_msg)) { ?>
					<p><?php eh($error_msg); ?></p>
					<?php } ?>
					<input type="submit" value="<?php eh($lang["loginLoginBtn"]) ?>" /><br />
				</form>
				<a href="<?php eh("$self?action=register"); ?>"><?php eh($lang["loginRegister"]) ?></a>
			</div>
		</div>
<?php include(ABSPATH.'templ'.DIRECTORY_SEPARATOR.'tpl.footer.php'); ?>
