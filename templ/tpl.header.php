<?php if(!defined("Z_PROTECTED")) exit; ?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>LDAP phonebook</title>
		<link type="text/css" href="templ/style.css" rel="stylesheet" />
	</head>
	<body>
		<ul class="menu-bar">
			<li><a href="/">Home</a></li>
			<?php if($uid == 1) { ?>
			<li><a href="?action=sync">Sync</a></li>
			<?php } ?>
			<ul style="float:right;list-style-type:none;">
				<?php if($uid) { ?>
				<li><a href="/logout/">Log Out</a></li>
				<?php } else { ?>
				<li><a href="/login/">Log In</a></li>
				<?php } ?>
			</ul>
		</ul>
