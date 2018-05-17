<?php include("tpl.header.php"); ?>
		<h3 align="center">Tools</h3>
		
		<a href="?action=hide_disabled">Hide contacts that was disabled in AD</a><br />
		<a href="?action=dump_db">Dump DB</a><br />
		<a href="?action=export_xml">Export XML</a><br />
		<a href="#" onclick="f_import_xml(); return false;">Import XML (replace existing contacts)</a><br />
		
		<form method="post" id="form-file-upload" name="form-file-upload">
			<input id="file-upload" type="file" name="file" style="display: none"/>
		</form>
<?php include("tpl.footer.php"); ?>
