<?php if(!defined('Z_PROTECTED')) exit; ?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<title>ERROR</title>
		<link type="text/css" href="<?php ls('templates/style.css') ?>" rel="stylesheet" />
	</head>
	<body>
		<h3 align="center"><?php L('Error') ?>:</h3>
		<pre><?php eh($error_msg); ?></pre>
	</body>
</html>
