<?php if(!defined("Z_PROTECTED")) exit; ?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>LDAP phonebook</title>
		<link type="text/css" href="templ/style.css" rel="stylesheet" />
		<link rel="stylesheet" href="templ/bootstrap.min.css" />
		<script src="templ/jquery-3.2.0.min.js"></script>
		<script src="templ/notify.min.js"></script>
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
