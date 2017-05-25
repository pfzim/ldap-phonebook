<?php if(!defined("Z_PROTECTED")) exit; ?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>LDAP phonebook</title>
		<link type="text/css" href="templ/style.css" rel="stylesheet" />
		<script src="templ/jquery-3.2.0.min.js"></script>
		<script src="templ/notify.min.js"></script>
		<script src="pb.js"></script>
	</head>
	<body>
		<ul class="menu-bar">
			<li><a href="<?php eh("$self"); ?>">Home</a></li>
			<li><a href="?action=map&amp;id=1">Map</a></li>
			<?php if($uid == 1) { ?>
			<li><a href="?action=sync">Sync</a></li>
			<li><a href="?action=export">Export</a></li>
			<?php } ?>
			<ul style="float:right;list-style-type:none;">
				<?php if($uid) { ?>
				<li><a href="?action=logoff">Log Out</a></li>
				<?php } else { ?>
				<li><a href="?action=login">Log In</a></li>
				<?php } ?>
			</ul>
		</ul>
