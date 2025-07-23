<?php if(!defined('Z_PROTECTED')) exit; ?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<title><?php L('Title') ?></title>
		<link rel="icon" type="image/png" sizes="32x32" href="<?php ls('templates/favicon-32x32.png') ?>">
		<link rel="icon" type="image/png" sizes="16x16" href="<?php ls('templates/favicon-16x16.png') ?>">
		<link type="text/css" href="<?php ls('templates/style.css') ?>" rel="stylesheet" />
		<link type="text/css" href="<?php ls('templates/flatpickr.material_red.css') ?>" rel="stylesheet" />
		<script src="<?php global $g_app_language; ls('languages/'.$g_app_language.'.js'); ?>"></script>
		<script src="<?php ls('pb.js') ?>"></script>
		<script src="<?php ls('flatpickr.min.js') ?>"></script>
		<script>
			is_admin = <?php echo ($core->UserAuth->check_permission(0, PB_ACCESS_ADMIN) ? '1' : '0'); ?>;
			g_link_prefix = '<?php echo WEB_LINK_PREFIX; ?>';
			g_link_static_prefix = '<?php echo WEB_LINK_STATIC_PREFIX; ?>';
		</script>
		<script type="text/javascript">
			document.documentElement.setAttribute("data-theme-color", localStorage.getItem("theme-color") || 'light');
		</script>
	</head>
	<body>
		<ul class="menu-bar">
			<li><a href="<?php ln('contacts') ?>"><?php L('Home') ?></a></li>
			<li><a href="<?php ln('map') ?>"><?php L('Map') ?></a></li>
			<?php if($core->UserAuth->get_id()) { ?>
				<li><a href="<?php ln('all') ?>"><?php L('ShowAll') ?></a></li>
				<li><a href="<?php ln('handshakes') ?>"><?php L('Handshakes') ?></a></li>
				<li><a href="<?php ln('tools') ?>"><?php L('Tools') ?></a></li>
				<li><a href="<?php ln('permissions') ?>"><?php L('Permissions') ?></a></li>
				<li><a href="<?php ln('users') ?>"><?php L('Users') ?></a></li>
			<?php } ?>

			<li class="right-menu-container">
				<ul class="right-menu">
				
					<li class="menu-item">
						<a href="#" onclick="return false;"><?php L('Theme') ?></a>
						<ul class="submenu">
							<li><a href="#" onclick="f_theme_change('dark'); return false;">dark</a></li>
							<li><a href="#" onclick="f_theme_change('light'); return false;">light</a></li>
						</ul>
					</li>

					<li class="menu-item">
						<a href="#" onclick="return false;"><?php L('Language') ?></a>
						<ul class="submenu">
							<?php
								$languages = languages_list();
								foreach($languages as $language)
								{
									echo '<li><a href="#" onclick="f_language_change(\'' . htmlspecialchars($language) . '\'); return false;">' . htmlspecialchars(strtoupper($language)) . '</a></li>';
								}
							?>
						</ul>
					</li>

					<?php if($core->UserAuth->get_id()) { ?>
						<li><a href="<?php ln('logoff') ?>"><?php L('Logout') ?> (<?php eh($core->UserAuth->get_login()); ?>)</a></li>
					<?php } else { ?>
						<li><a href="<?php ln('login') ?>"><?php L('LoginBtn') ?></a></li>
					<?php } ?>
				</ul>
			</li>
		</ul>
