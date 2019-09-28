<?php
/*
    LDAP-phonebook - simple LDAP phonebook
    Copyright (C) 2016 Dmitry V. Zimin

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if (!defined('ABSPATH'))
{
	define('ABSPATH', dirname(__FILE__).DIRECTORY_SEPARATOR);
}
	
if(!file_exists(ABSPATH.'inc.config.php'))
{
	header('Location: install.php');
	exit;
}

require_once(ABSPATH.'inc.config.php');


	if(!isset($_GET['action']) || ($_GET['action'] != 'upgrade'))
	{
		header("Content-Type: text/html; charset=utf-8");
?><!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<title>Upgrade LDAP phonebook</title>
	</head>
	<body>
	Run <a href="?action=upgrade">upgrade</a>
	</body>
</html>
<?php
		exit;
	}

	header("Content-Type: text/plain; charset=utf-8");

	require_once('inc.db.php');
	//require_once('inc.dbfunc.php');
	require_once('inc.utils.php');

	$db = new MySQLDB(DB_HOST, NULL, DB_USER, DB_PASSWD, DB_NAME, DB_CPAGE, FALSE);
	//$db->connect();
	
	$config = array('db_version' => 0);

	if($db->select(rpv("SELECT m.`name`, m.`value` FROM @config AS m WHERE m.`name` = 'db_version'")))
	{
		foreach($db->data as $row)
		{
			$config[$row[0]] = $row[1];
		}
	}

	echo "Upgrading...\n";

	switch(intval($config['db_version']))
	{
		case 0:
		{
			echo "\nCreate 'config' table...\n";
			if(!$db->put(rpv("CREATE TABLE @config (`name` VARCHAR(255) NOT NULL DEFAULT '', `value` VARCHAR(8192) NOT NULL DEFAULT '', PRIMARY KEY(`name`)) ENGINE = InnoDB")))
			{
				echo 'Error: '.$db->get_last_error()."\n";
			}
			echo "Add column 'ldap' to 'users' table...\n";
			if(!$db->put(rpv("ALTER TABLE @users ADD COLUMN `ldap` INTEGER UNSIGNED NOT NULL DEFAULT 0 AFTER `mail`")))
			{
				echo 'Error: '.$db->get_last_error()."\n";
			}
			echo "Set db_version = '1'...\n";
			if(!$db->put(rpv("INSERT INTO @config (`name`, `value`) VALUES('db_version', 1) ON DUPLICATE KEY UPDATE `value` = 1")))
			{
				echo 'Error: '.$db->get_last_error()."\n";
			}
			echo "Upgrade to version 1 complete!\n";
		}
		case 1:
		{
			echo "\nAdd column 'type' to 'contacts' table...\n";
			if(!$db->put(rpv("ALTER TABLE @contacts ADD COLUMN `type` INTEGER UNSIGNED NOT NULL DEFAULT 0 AFTER `photo`")))
			{
				echo 'Error: '.$db->get_last_error()."\n";
			}
			echo "Set db_version = '2'...\n";
			if(!$db->put(rpv("UPDATE @config SET `value` = 2 WHERE `name` = 'db_version' LIMIT 1")))
			{
				echo 'Error: '.$db->get_last_error()."\n";
			}
			echo "\nNow you must add to inc.config.php something like\n\n";
			echo '  $g_icons = array("Human", "Printer", "Fax");';
			echo "\nAnd replace files templ/marker-static-[0-nn].png with you icons\n";
			echo "\n\nUpgrade to version 2 complete!\n";
		}
		case 2:
		{
			echo "\nCreate 'handshake' table...\n";
			if(!$db->put(rpv("CREATE TABLE @handshake (`id` int(10) unsigned NOT NULL AUTO_INCREMENT, `user` VARCHAR(255) NOT NULL, `date` DATETIME NOT NULL, `computer` VARCHAR(255) NOT NULL DEFAULT '', `ip` VARCHAR(255) NOT NULL DEFAULT '', PRIMARY KEY(`id`)) ENGINE = InnoDB")))
			{
				echo 'Error: '.$db->get_last_error()."\n";
			}
			echo "Set db_version = '3'...\n";
			if(!$db->put(rpv("UPDATE @config SET `value` = 3 WHERE `name` = 'db_version' LIMIT 1")))
			{
				echo 'Error: '.$db->get_last_error()."\n";
			}
			echo "\n\nUpgrade to version 3 complete!\n";
		}
		case 3:
		{
			echo "\nAdd column 'pcity' to 'contacts' table...\n";
			if(!$db->put(rpv("ALTER TABLE @contacts ADD COLUMN `pcity` varchar(255) NOT NULL DEFAULT '' AFTER `pint`")))
			{
				echo 'Error: '.$db->get_last_error()."\n";
			}
			echo "Set db_version = '4'...\n";
			if(!$db->put(rpv("UPDATE @config SET `value` = 4 WHERE `name` = 'db_version' LIMIT 1")))
			{
				echo 'Error: '.$db->get_last_error()."\n";
			}
			echo "\nNow you must add to inc.config.php something like\n\n";
			echo '  define("APP_LANGUAGE", "en");';
			echo "\n\nUpgrade to version 4 complete!\n";
		}
		break;
		case 4:
		{
			echo "Upgrade doesn't required\n";
		}
		break;
		default:
		{
			echo "ERROR: Unknown DB version\n";
		}
	}
