<?php
/*
    LDAP-phonebook - simple LDAP phonebook
    Copyright (C) 2016-2020 Dmitry V. Zimin

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

if(!defined('ROOT_DIR'))
{
	define('ROOT_DIR', dirname(__FILE__).DIRECTORY_SEPARATOR);
	define('TEMPLATES_DIR', ROOT_DIR.'templates'.DIRECTORY_SEPARATOR);
	define('MODULES_DIR', ROOT_DIR.'modules'.DIRECTORY_SEPARATOR);
	define('ROUTES_DIR', ROOT_DIR.'routes'.DIRECTORY_SEPARATOR);
}

if(!file_exists(ROOT_DIR.'inc.config.php'))
{
	header('Location: install.php');
	exit;
}

require_once(ROOT_DIR.'inc.config.php');


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

	if(!defined('DB_RW_HOST') || !defined('DB_USER') || !defined('DB_PASSWD') || !defined('DB_NAME') || !defined('DB_CPAGE') || !defined('DB_PREFIX') || !defined('USE_LDAP') || !defined('PB_LDAP_FILTER') || !defined('WEB_URL'))
	{
		echo "Missing parameters in inc.config.php:\n\n";
		if(!defined('DB_RW_HOST')) { echo "  define('DB_RW_HOST', 'localhost');\n"; }
		if(!defined('DB_USER')) { echo "  define('DB_USER', 'root');\n"; }
		if(!defined('DB_PASSWD')) { echo "  define('DB_PASSWD', '');\n"; }
		if(!defined('DB_NAME')) { echo "  define('DB_NAME', 'pb');\n"; }
		if(!defined('DB_CPAGE')) { echo "  define('DB_CPAGE', 'utf8');\n"; }
		if(!defined('DB_PREFIX')) { echo "  define('DB_PREFIX', 'pb_');\n"; }
		if(!defined('USE_LDAP')) { echo "  define('USE_LDAP', TRUE);\n"; }
		if(!defined('PB_LDAP_FILTER')) { echo "  define('PB_LDAP_FILTER', '(objectCategory=person)');\n"; }
		if(!defined('WEB_URL')) { echo "  define('WEB_URL', 'https://pb.contoso.com/pb/');\n"; }
		exit;
	}

	if(USE_LDAP && (!defined('LDAP_URI') || !defined('LDAP_BASE_DN')))
	{
		echo "Missing parameters in inc.config.php:\n\n";
		if(!defined('LDAP_URI')) { echo "  define('LDAP_URI', 'ldap://dc-01.contoso.com ldap://dc-02.contoso.com:389 ldaps://dc-03.contoso.com');\n"; }
		if(!defined('LDAP_BASE_DN')) { echo "  define('LDAP_BASE_DN', 'DC=domain,DC=local');\n"; }
		exit;
	}

	require_once(ROOT_DIR.'inc.utils.php');
	require_once(ROOT_DIR.'modules'.DIRECTORY_SEPARATOR.'Core.php');

	$core = new Core(TRUE);
	$core->load_ex('db', 'MySQLDB');
	$core->load('UserAuth');

	if(!$core->db->select_ex($data, rpv("SHOW COLUMNS FROM @config LIKE 'uid'")))
	{
		if(!$core->db->put(rpv('ALTER TABLE `@config` ADD COLUMN `uid` INT NOT NULL DEFAULT 0 FIRST')))
		{
			echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
		}
	}

	/*
	$config = array('db_version' => 0);
	if($core->db->select_ex($data, rpv("SELECT m.`name`, m.`value` FROM @config AS m WHERE m.`name` = 'db_version' LIMIT 1")))
	{
		foreach($data as &$row)
		{
			$config[$row[0]] = $row[1];
		}
	}
	*/

	echo "Upgrading...\n";

	//switch(intval($config['db_version']))
	switch(intval($core->Config->get_global('db_version', 0)))
	{
		case 0:
		{
			echo "\nCreate 'config' table...\n";
			if(!$core->db->put(rpv("CREATE TABLE @config (`name` VARCHAR(255) NOT NULL DEFAULT '', `value` VARCHAR(8192) NOT NULL DEFAULT '', PRIMARY KEY(`name`)) ENGINE = InnoDB")))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}
			echo "Add column 'ldap' to 'users' table...\n";
			if(!$core->db->put(rpv("ALTER TABLE @users ADD COLUMN `ldap` INTEGER UNSIGNED NOT NULL DEFAULT 0 AFTER `mail`")))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}
			echo "Set db_version = '1'...\n";
			if(!$core->db->put(rpv("INSERT INTO @config (`name`, `value`) VALUES('db_version', 1) ON DUPLICATE KEY UPDATE `value` = 1")))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}
			echo "Upgrade to version 1 complete!\n";
		}
		case 1:
		{
			echo "\nAdd column 'type' to 'contacts' table...\n";
			if(!$core->db->put(rpv("ALTER TABLE @contacts ADD COLUMN `type` INTEGER UNSIGNED NOT NULL DEFAULT 0 AFTER `photo`")))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}
			echo "Set db_version = '2'...\n";
			if(!$core->db->put(rpv("UPDATE @config SET `value` = 2 WHERE `name` = 'db_version' LIMIT 1")))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}
			echo "\nNow you must add to inc.config.php something like\n\n";
			echo '  $g_icons = array("Human", "Printer", "Fax");';
			echo "\nAnd replace files templ/marker-static-[0-nn].png with you icons\n";
			echo "\n\nUpgrade to version 2 complete!\n";
		}
		case 2:
		{
			echo "\nCreate 'handshake' table...\n";
			if(!$core->db->put(rpv("CREATE TABLE @handshake (`id` int(10) unsigned NOT NULL AUTO_INCREMENT, `user` VARCHAR(255) NOT NULL, `date` DATETIME NOT NULL, `computer` VARCHAR(255) NOT NULL DEFAULT '', `ip` VARCHAR(255) NOT NULL DEFAULT '', PRIMARY KEY(`id`)) ENGINE = InnoDB")))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}
			echo "Set db_version = '3'...\n";
			if(!$core->db->put(rpv("UPDATE @config SET `value` = 3 WHERE `name` = 'db_version' LIMIT 1")))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}
			echo "\n\nUpgrade to version 3 complete!\n";
		}
		case 3:
		{
			echo "\nAdd column 'pcity' to 'contacts' table...\n";
			if(!$core->db->put(rpv("ALTER TABLE @contacts ADD COLUMN `pcity` varchar(255) NOT NULL DEFAULT '' AFTER `pint`")))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}
			echo "Set db_version = '4'...\n";
			if(!$core->db->put(rpv("UPDATE @config SET `value` = 4 WHERE `name` = 'db_version' LIMIT 1")))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}
			echo "\nNow you must add to inc.config.php something like\n\n";
			echo '  define("APP_LANGUAGE", "en");';
			echo "\n\nUpgrade to version 4 complete!\n";
		}
		case 4:
		{
			echo "Reset all users passwords...\n";
			if(!$core->db->put(rpv("UPDATE @users SET `passwd` = MD5('admin') WHERE `ldap` = 0")))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			echo "Add column `flags` to table `@users`...\n";
			if(!$core->db->put(rpv("ALTER TABLE `@users` ADD COLUMN `flags` INTEGER UNSIGNED NOT NULL DEFAULT 0 AFTER `sid`")))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			echo "Copy `deleted` to `flags`...\n";
			if(!$core->db->put(rpv("UPDATE @users SET `flags` = (`flags` | 0x0001) WHERE `deleted` <> 0")))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			echo "Copy `ldap` to `flags`...\n";
			if(!$core->db->put(rpv("UPDATE @users SET `flags` = (`flags` | 0x0002) WHERE `ldap` <> 0")))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			echo "Remove column `deleted` from table `@users`...\n";
			if(!$core->db->put(rpv("ALTER TABLE `@users` DROP COLUMN `deleted`")))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			echo "Remove column `ldap` from table `@users`...\n";
			if(!$core->db->put(rpv("ALTER TABLE `@users` DROP COLUMN `ldap`")))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			echo "Create table `@access`...\n";
			if(!$core->db->put(rpv("
				CREATE TABLE  `@access` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `dn` varchar(1024) NOT NULL DEFAULT '',
				  `oid` int(10) unsigned NOT NULL DEFAULT 0,
				  `allow_bits` binary(32) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			")))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			echo "Set db_version = '5'...\n";
			if(!$core->db->put(rpv("UPDATE @config SET `value` = 5 WHERE `name` = 'db_version' LIMIT 1")))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			echo "\nNow you must add to inc.config.php something like\n\n";
			echo "  define('LDAP_URI', 'ldap://dc-01.example.org');\n";
			echo "  LDAP_HOST and LDAP_PORT deprecated and can be removed.\n";

			echo "\n*******************************************************";
			echo "\n*  For all local users passwords now set to 'admin'.  *";
			echo "\n*  You must reset all internal users passwords,       *";
			echo "\n*  because PASSWORD function deprecated in MySQL.     *";
			echo "\n*******************************************************";
			echo "\n\nUpgrade to version 5 complete!\n";
		}
		case 5:
		{
			//echo "Upgrade not yet supported. Please reinstall\n";
			//break;

			if(!$core->db->put(rpv('
				CREATE TABLE `@access` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`sid` varchar(256) NOT NULL DEFAULT \'\',
					`dn` varchar(1024) NOT NULL DEFAULT \'\',
					`oid` int(10) unsigned NOT NULL DEFAULT 0,
					`allow_bits` binary(32) NOT NULL DEFAULT \'\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\\0\',
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8
			')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			if(!$core->db->put(rpv('
				CREATE TABLE `@logs` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`date` datetime NOT NULL,
					`uid` int(10) unsigned NOT NULL,
					`operation` varchar(1024) NOT NULL,
					`params` varchar(4096) NOT NULL,
					`flags` int(10) unsigned NOT NULL,
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8
			')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			if(!$core->db->put(rpv('ALTER TABLE `@handshake` RENAME TO `@handshakes`')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			if(!$core->db->put(rpv('ALTER TABLE `@users` MODIFY COLUMN `sid` VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			if(!$core->db->put(rpv('ALTER TABLE `@users` ADD COLUMN `reset_token` VARCHAR(16) NULL AFTER `sid`')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			/*
			if(!$core->db->put(rpv('ALTER TABLE `@config` ADD COLUMN `uid` INT NOT NULL DEFAULT 0 FIRST')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}
			*/

			if(!$core->db->put(rpv('ALTER TABLE `@contacts` ADD COLUMN `adid` VARCHAR(32) NOT NULL DEFAULT \'\' AFTER `id`')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			if(!$core->db->put(rpv('ALTER TABLE `@contacts` CHANGE COLUMN `samname` `samaccountname` VARCHAR(20) NOT NULL DEFAULT \'\'')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			if(!$core->db->put(rpv('ALTER TABLE `@contacts` CHANGE COLUMN `fname` `first_name` VARCHAR(255) NOT NULL DEFAULT \'\'')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			if(!$core->db->put(rpv('ALTER TABLE `@contacts` CHANGE COLUMN `lname` `last_name` VARCHAR(255) NOT NULL DEFAULT \'\'')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			if(!$core->db->put(rpv('ALTER TABLE `@contacts` ADD COLUMN `middle_name` VARCHAR(255) NOT NULL DEFAULT \'\' AFTER `last_name`')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			if(!$core->db->put(rpv('ALTER TABLE `@contacts` CHANGE COLUMN `dep` `department` VARCHAR(255) NOT NULL DEFAULT \'\'')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			if(!$core->db->put(rpv('ALTER TABLE `@contacts` CHANGE COLUMN `org` `organization` VARCHAR(255) NOT NULL DEFAULT \'\'')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			if(!$core->db->put(rpv('ALTER TABLE `@contacts` CHANGE COLUMN `pos` `position` VARCHAR(255) NOT NULL DEFAULT \'\'')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			if(!$core->db->put(rpv('ALTER TABLE `@contacts` CHANGE COLUMN `pint` `phone_internal` VARCHAR(255) NOT NULL DEFAULT \'\'')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			if(!$core->db->put(rpv('ALTER TABLE `@contacts` CHANGE COLUMN `pcity` `phone_external` VARCHAR(255) NOT NULL DEFAULT \'\'')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			if(!$core->db->put(rpv('ALTER TABLE `@contacts` CHANGE COLUMN `pcell` `phone_mobile` VARCHAR(255) NOT NULL DEFAULT \'\'')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			if(!$core->db->put(rpv('ALTER TABLE `@contacts` CHANGE COLUMN `bday` `birthday` DATE NULL DEFAULT NULL')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			if(!$core->db->put(rpv('ALTER TABLE `@contacts` ADD COLUMN `reserved1` VARCHAR(255) NOT NULL DEFAULT \'\' AFTER `birthday`')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			if(!$core->db->put(rpv('ALTER TABLE `@contacts` ADD COLUMN `reserved2` VARCHAR(255) NOT NULL DEFAULT \'\' AFTER `reserved1`')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			if(!$core->db->put(rpv('ALTER TABLE `@contacts` ADD COLUMN `reserved3` VARCHAR(255) NOT NULL DEFAULT \'\' AFTER `reserved2`')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			if(!$core->db->put(rpv('ALTER TABLE `@contacts` ADD COLUMN `reserved4` VARCHAR(255) NOT NULL DEFAULT \'\' AFTER `reserved3`')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			if(!$core->db->put(rpv('ALTER TABLE `@contacts` ADD COLUMN `reserved5` VARCHAR(255) NOT NULL DEFAULT \'\' AFTER `reserved4`')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			if(!$core->db->put(rpv('ALTER TABLE `@contacts` ADD COLUMN `flags` INT NOT NULL DEFAULT 0 AFTER `reserved5`')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			/*
			if(!$core->db->put(rpv('ALTER TABLE `@contacts` CHANGE COLUMN `visible` `flags` int(10) unsigned NOT NULL DEFAULT 0')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}
			*/

			if(!$core->db->put(rpv('UPDATE @contacts SET `flags` = (`flags` | 0x0001) WHERE `visible` = 1')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			if(!$core->db->put(rpv('UPDATE @contacts SET `flags` = (`flags` | 0x0008) WHERE `photo` = 1')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			if(!$core->db->put(rpv('ALTER TABLE `@contacts` DROP COLUMN `photo`')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}


			if(!$core->db->put(rpv('ALTER TABLE `@contacts` DROP COLUMN `visible`')))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			if(!$core->db->put(rpv("UPDATE @config SET `value` = 6 WHERE `uid` = 0 AND `name` = 'db_version' LIMIT 1")))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}

			if(defined('USE_LDAP') && USE_LDAP)
			{
				$count_updated = 0;
				$cookie = '';

				do
				{
					$result = ldap_search(
						$core->LDAP->get_link(),
						LDAP_BASE_DN,
						PB_LDAP_FILTER,
						['objectguid', 'samaccountname'],
						0,
						0,
						0,
						LDAP_DEREF_NEVER,
						[['oid' => LDAP_CONTROL_PAGEDRESULTS, 'value' => ['size' => 200, 'cookie' => $cookie]]]
					);

					if($result === FALSE)
					{
						echo 'ERROR['.__LINE__.']: ldap_search return: '.ldap_error($core->LDAP->get_link()).PHP_EOL;
						exit;
					}

					if(!ldap_parse_result($core->LDAP->get_link(), $result, $errcode , $matcheddn , $errmsg , $referrals, $controls))
					{
						echo 'ERROR['.__LINE__.']: ldap_parse_result return: '.ldap_error($core->LDAP->get_link()).PHP_EOL;
						exit;
					}

					$entries = ldap_get_entries($core->LDAP->get_link(), $result);
					if($entries === FALSE)
					{
						echo 'ERROR['.__LINE__.']: ldap_get_entries return: '.ldap_error($core->LDAP->get_link()).PHP_EOL;
						exit;
					}

					$i = $entries['count'];

					while($i > 0)
					{
						$i--;
						if(!empty($entries[$i]['samaccountname'][0]))
						{
							$v_adid = bin2hex(@$entries[$i]['objectguid'][0]);  // unique active directory id
							$v_samaccountname = @$entries[$i]['samaccountname'][0];

							if($core->db->select_ex($data, rpv("
									SELECT
										c.`id`,
										c.`adid`
									FROM
										`@contacts` AS c
									WHERE
										c.`samaccountname` = !
									LIMIT 1
								",
								$v_samaccountname
							)))
							{
								$v_id = &$data[0][0];
								$core->db->put(rpv('
										UPDATE `@contacts` SET
											`adid` = !
										WHERE
											`id` = #
										LIMIT 1
									',
									$v_adid,
									$v_id
								));

								$count_updated++;
							}
						}
					}

					if(isset($controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie']))
					{
						$cookie = $controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'];
					}
					else
					{
						$cookie = '';
					}

					ldap_free_result($result);
				}
				while(!empty($cookie));

				echo 'Unique ID updated for '.$count_updated.' contacts'.PHP_EOL.PHP_EOL;
			}

			echo "\n**************************************************************************";
			echo "\n*  Please, reset password for local accounts using Reset password link!  *";
			echo "\n**************************************************************************";
			echo "\n\nNow you must update inc.config.php\n\n";
			echo "  rename DB_HOST to DB_RW_HOST\n";
			echo "  add define('USE_LDAP', TRUE);\n";
			echo "  add define('PB_LDAP_FILTER', '(objectCategory=person)');\n";
			echo "  add define('MAIL_VERIFY_PEER', TRUE);\n";
			echo "  add define('MAIL_VERIFY_PEER_NAME', TRUE);\n";
			echo "  add define('MAIL_ALLOW_SELF_SIGNED', FALSE);\n";
			echo "  and other missing parameters look at examples/inc.config.php.example\n";
			echo "\n\nUpgrade to version 6 complete!\n";
		}
		case 6:
		{
			echo "Update users flags...\n";
			if(!$core->db->put(rpv("UPDATE @users SET `flags` = (`flags` | {%UA_ADMIN}) WHERE (`flags` & {%UA_LDAP}) = 0")))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}
			echo "Set db_version = '7'...\n";
			if(!$core->db->put(rpv("UPDATE @config SET `value` = 7 WHERE `name` = 'db_version' LIMIT 1")))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}
			echo "\n\nUpgrade to version 7 complete!\n";
		}
		break;

		case 7:
		{
			echo "Adding column `description` to table @config...\n";
			if(!$core->db->put(rpv("ALTER TABLE `@config` ADD COLUMN `description` VARCHAR(2048) DEFAULT NULL AFTER `value`")))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}
			echo "Add new config parameter 'maps_count'...\n";
			if(!$core->db->put(rpv("INSERT INTO @config (`uid`, `name`, `value`, `description`) VALUES (0, 'maps_count', '".PB_MAPS_COUNT."', 'Maps count')")))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}
			echo "Set db_version = '8'...\n";
			if(!$core->db->put(rpv("UPDATE @config SET `value` = 8, `description` = 'DB schema version. Do not change!' WHERE `name` = 'db_version' LIMIT 1")))
			{
				echo 'ERROR['.__LINE__.']: '.$core->db->get_last_error().PHP_EOL;
			}
			echo "\n\nUpgrade to version 8 complete!\n";

			for($i = 1; $i <= PB_MAPS_COUNT; $i++)
			{
				copy(ROOT_DIR.'templates'.DIRECTORY_SEPARATOR.'map'.$i.'.jpg', ROOT_DIR.'photos'.DIRECTORY_SEPARATOR.'map'.$i.'.jpg');
			}
		}
		break;

		case 8:
		{
			echo "Upgrade doesn't required\n";
		}
		break;

		default:
		{
			echo "ERROR: Unknown DB version\n";
		}
	}
