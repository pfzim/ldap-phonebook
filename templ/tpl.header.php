<?php if(!defined('Z_PROTECTED')) exit; ?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<title><?php eh($lang["headerLDAPPhonebook"]) ?></title>
		<link type="text/css" href="templ/style.css" rel="stylesheet" />
		<link rel="icon" type="image/png" sizes="32x32" href="templ/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="templ/favicon-16x16.png">
		<script src="pb.js"></script>
	</head>
	<body>
		<ul class="menu-bar">
			<li><a href="<?php eh("$self"); ?>"><?php eh($lang["headerHome"]) ?></a></li>
			<li><a href="?action=map&amp;id=1"><?php eh($lang["headerMap"]) ?></a></li>
			<?php if($uid) { ?>
			<li><a href="?action=all"><?php eh($lang["headerShowAll"]) ?></a></li>
			<li><a href="?action=handshakes"><?php eh($lang["headerHandshakes"]) ?></a></li>
			<li><a href="?action=sync"><?php eh($lang["headerSync"]) ?></a></li>
			<li><a href="?action=export"><?php eh($lang["headerExport"]) ?></a></li>
			<li><a href="?action=services"><?php eh($lang["headerTools"]) ?></a></li>
			<?php } ?>
			<ul style="float:right;list-style-type:none;">
				<?php if($uid) { ?>
				<li><a href="?action=logoff"><?php eh($lang["headerLogOut"]) ?></a></li>
				<?php } else { ?>
				<li><a href="?action=login"><?php eh($lang["headerLogIn"]) ?></a></li>
				<?php } ?>
			</ul>
		</ul>
