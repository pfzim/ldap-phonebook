<?php include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.header.php'); ?>
		<h3 align="center"><?php eh($lang["servicesTools"]) ?></h3>
		
		<a href="?action=passwd_form"><?php eh('Change password') ?></a><br />
		<a href="?action=hide_disabled"><?php eh($lang["servicesHideСontacts"]) ?></a><br />
		<a href="?action=dump_db"><?php eh($lang["servicesDumpDB"]) ?></a><br />
		<a href="?action=export_xml"><?php eh($lang["servicesExportXML"]) ?></a><br />
		<a href="#" onclick="f_import_xml(); return false;"><?php eh($lang["servicesImportXML"]) ?></a><br />
		
		<p>
			Current user token: <?php eh($user->get_token()); ?><br />
			The token will be reset if you logout.
		</p>
		<p>Example usage:</p>
		<pre>
			curl --silent --cookie <?php eh('"zl='.$user->get_login().';zh='.$user->get_token().'"'); ?>" --output /dev/null "http://localhost<?php eh($_SERVER['PHP_SELF'].'?action=sync'); ?>"
		</pre>
		
		<form method="post" id="form-file-upload" name="form-file-upload">
			<input id="file-upload" type="file" name="file" style="display: none"/>
		</form>
<?php include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.footer.php'); ?>
