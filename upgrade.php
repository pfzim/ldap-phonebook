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

if(!file_exists('inc.config.php'))
{
	header('Location: install.php');
	exit;
}

require_once("inc.config.php");

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

	$db = new MySQLDB(DB_RW_HOST, NULL, DB_USER, DB_PASSWD, DB_NAME, DB_CPAGE, FALSE);
	//$db->connect();
	
	$config = array('db_version' => 0);

	if($db->select(rpv("SELECT m.`name`, m.`value` FROM @config AS m WHERE m.`name` = 'db_version'")))
	{
		foreach($db->data as $row)
		{
			$config[$row[0]] = $row[1];
		}
	}

	switch(intval($config['db_version']))
	{
		case 0:
		{
			echo "Create 'config' table...\n";
			if(!$db->put(rpv("CREATE TABLE @config (`name` VARCHAR(255) NOT NULL DEFAULT '', `value` VARCHAR(8192) NOT NULL DEFAULT '', PRIMARY KEY(`name`)) ENGINE = InnoDB")))
			{
				echo 'Error: '.$db->get_last_error();
			}
			echo "Add column 'ldap' to 'users' table...\n";
			if(!$db->put(rpv("ALTER TABLE @users ADD COLUMN `ldap` INTEGER UNSIGNED NOT NULL DEFAULT 0 AFTER `mail`")))
			{
				echo 'Error: '.$db->get_last_error();
			}
			echo "Set db_version = '1'...\n";
			if(!$db->put(rpv("INSERT INTO @config (`name`, `value`) VALUES('db_version', 1) ON DUPLICATE KEY UPDATE `value` = 1")))
			{
				echo 'Error: '.$db->get_last_error();
			}
			echo "Upgrade to version 1 complete!\n";
		}
		case 1:
		{
			echo "Add column 'type' to 'contacts' table...\n";
			if(!$db->put(rpv("ALTER TABLE @users ADD COLUMN `type` INTEGER UNSIGNED NOT NULL DEFAULT 0 AFTER `photo`")))
			{
				echo 'Error: '.$db->get_last_error();
			}
			echo "Set db_version = '2'...\n";
			if(!$db->put(rpv("UPDATE @config SET `value` = 2 WHERE `name` = 'db_version' LIMIT 1")))
			{
				echo 'Error: '.$db->get_last_error();
			}
			echo "Upgrade to version 2 complete!\n";
		}
		break;
		case 2:
		{
			echo "Upgrade doesn't required\n";
		}
		break;
		default:
		{
			echo "ERROR: Unknown DB version\n";
		}
	}
