<?php include(ABSPATH.'templ'.DIRECTORY_SEPARATOR.'tpl.header.php'); ?>
		<h3 align="center"><?php eh($lang["servicesTools"]) ?></h3>
		
		<a href="?action=hide_disabled"><?php eh($lang["servicesHideСontacts"]) ?></a><br />
		<a href="?action=dump_db"><?php eh($lang["servicesDumpDB"]) ?></a><br />
		<a href="?action=export_xml"><?php eh($lang["servicesExportXML"]) ?></a><br />
		<a href="#" onclick="f_import_xml(); return false;"><?php eh($lang["servicesImportXML"]) ?></a><br />
		
		<form method="post" id="form-file-upload" name="form-file-upload">
			<input id="file-upload" type="file" name="file" style="display: none"/>
		</form>
<?php include(ABSPATH.'templ'.DIRECTORY_SEPARATOR.'tpl.footer.php'); ?>
