<?php include("tpl.header.php"); ?>
		<h3 align="center">Debug info:</h3>
		<pre><?php eh("ERR: $error_msg"); ?></pre>
		<pre><?php print_r($res); ?></pre>
<?php include("tpl.footer.php"); ?>

