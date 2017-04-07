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

	session_name("ZID");
	session_start();
	error_reporting(E_ALL);
	define("Z_PROTECTED", "YES");

	header("Content-Type: text/html; charset=utf-8");

	$self = $_SERVER['PHP_SELF'];

	$uid = 0;
	if(isset($_SESSION['uid']))
	{
		$uid = $_SESSION['uid'];
	}

	$uid = 1;

	if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = @$_SERVER['REMOTE_ADDR'];
	}

	require_once('inc.db.php');
	//require_once('inc.dbfunc.php');
	require_once('inc.utils.php');

	$action = "";
	if(isset($_GET['action']))
	{
		$action = $_GET['action'];
	}

	$id = 0;
	if(isset($_GET['id']))
	{
		$id = $_GET['id'];
	}

	$db = new MySQLDB();
		
	if(empty($uid))
	{
		if(!empty(@$_COOKIE['zh']) && !empty(@$_COOKIE['zl']))
		{
			$db->connect();
			
			if($db->select(rpv("SELECT m.`id` FROM pb_users AS m WHERE m.`login` = ! AND m.`sid` IS NOT NULL AND m.`sid` = ! AND m.`deleted` = 0 LIMIT 1", $_COOKIE['zl'], $_COOKIE['zh'])))
			{
				$_SESSION['uid'] = $db->data[0][0];
				$uid = $_SESSION['uid'];
				setcookie("zh", @$_COOKIE['zh'], time()+2592000, '/');
				setcookie("zl", @$_COOKIE['zl'], time()+2592000, '/');
			}
			$db->disconnect();
		}
	}

	switch($action)
	{
		case 'sync':
		{
			header("Content-Type: text/plain;");
			$ldap = ldap_connect(LDAP_HOST, LDAP_PORT);
			if($ldap)
			{
				ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
				ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
				if(ldap_bind($ldap, LDAP_USER, LDAP_PASSWD))
				{
					$db->connect();
					$finfo = new finfo(FILEINFO_MIME_TYPE);

					$cookie = '';
					do
					{
						ldap_control_paged_result($ldap, 200, true, $cookie);
						
						$sr = ldap_search($ldap, LDAP_BASE_DN, LDAP_FILTER, explode(',', LDAP_ATTRS));
						if($sr)
						{
							$records = ldap_get_entries($ldap, $sr);
							foreach($records as $account)
							{
								if(!empty($account['givenname'][0]) && !empty($account['sn'][0]))
								{
									/*
									echo @$account['samaccountname'][0];
									echo ' '.@$account['sn'][0];
									echo ' '.@$account['givenname'][0];
									//echo ' '.@$account['name'][0];
									echo ' '.@$account['displayname'][0];
									echo ' '.@$account['mail'][0];
									echo ' '.@$account['telephonenumber'][0];
									echo ' '.@$account['mobile'][0];
									echo ' '.@$account['description'][0];
									echo ' '.@$account['title'][0];
									echo ' '.@$account['department'][0];
									echo ' '.@$account['company'][0];
									echo ' '.@$account['info'][0];
									echo "\n";
									/**/

									$mime = '';
									print_r($account);
									if(isset($account['thumbnailphoto'][0]))
									{
										$mime = $finfo->buffer($account['thumbnailphoto'][0]);
										echo "MIME: ".$mime . "\n";
									}

									if($db->select(rpv("SELECT m.`samname` FROM `pb_contacts` AS m WHERE m.`samname` = ! LIMIT 1", $account['samaccountname'][0])))
									{
										$db->put(rpv("UPDATE `pb_contacts` SET `fname` = !, `lname` = !, `dep` = !, `org` = !, `pos` = !, `pint` = !, `pcell` = !, `mail` = !, `mime` = !, `photo` = ! WHERE `samname` = ! LIMIT 1", @$account['givenname'][0], @$account['sn'][0], @$account['department'][0], @$account['company'][0], @$account['title'][0], @$account['telephonenumber'][0], @$account['mobile'][0], @$account['mail'][0], $mime, base64_encode(@$account['thumbnailphoto'][0]), @$account['samaccountname'][0]));
									}
									else
									{
										$db->put(rpv("INSERT INTO `pb_contacts` (`samname`, `fname`, `lname`, `dep`, `org`, `pos`, `pint`, `pcell`, `mail`, `mime`, `photo`, `visible`) VALUES (!, !, !, !, !, !, !, !, !, !, !, 1)", @$account['samaccountname'][0], @$account['givenname'][0], @$account['sn'][0], @$account['department'][0], @$account['company'][0], @$account['title'][0], @$account['telephonenumber'][0], @$account['mobile'][0], @$account['mail'][0], $mime, base64_encode(@$account['thumbnailphoto'][0])));
									}
									//echo "\r\n".$db->get_last_error()."\r\n";
								}
							}
							ldap_control_paged_result_response($ldap, $sr, $cookie);
							ldap_free_result($sr);
						}

					}
					while($cookie !== null && $cookie != '');

					$db->disconnect();
					ldap_unbind($ldap);
				}
			}
			exit;
		}
		case 'hide':
			$db->connect();
			$db->put(rpv("UPDATE `pb_contacts` SET `visible` = 0 WHERE `id` = # LIMIT 1", $id));
			$db->disconnect();
			echo '{"result": 0, "message": "Successful hide (ID '.$id.')"}';
			exit;
	}

	$db->connect();
	
	$db->select(rpv("SELECT m.`id`, m.`samname`, m.`fname`, m.`lname`, m.`dep`, m.`org`, m.`pos`, m.`pint`, m.`pcell`, m.`mail`, m.`mime`, m.`photo` FROM `pb_contacts` AS m WHERE m.`visible` = 1 ORDER BY m.`lname`, m.`fname`", array()));
	//$res = $db->data;
	$db->disconnect();
	include('templ/tpl.main.php');
	//include('templ/tpl.debug.php');
