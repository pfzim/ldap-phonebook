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

	include('inc.dbfunc.php');
	include('inc.utils.php');

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

	if(empty($uid))
	{
		if(!empty(@$_COOKIE['zh']) && !empty(@$_COOKIE['zl']))
		{
			db_connect();
			$query = rpv_v2("SELECT m.`id` FROM pb_users AS m WHERE m.`login` = ! AND m.`sid` IS NOT NULL AND m.`sid` = ! AND m.`deleted` = 0 LIMIT 1", array($_COOKIE['zl'], $_COOKIE['zh']));
			$res = db_select($query);
			db_disconnect();
			if($res !== FALSE)
			{
				$_SESSION['uid'] = $res[0][0];
				$uid = $_SESSION['uid'];
				setcookie("zh", @$_COOKIE['zh'], time()+2592000, '/');
				setcookie("zl", @$_COOKIE['zl'], time()+2592000, '/');
			}
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
					$sr = ldap_search($ldap, LDAP_BASE_DN, LDAP_FILTER, explode(',', LDAP_ATTRS));
					if($sr)
					{
						db_connect();
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

								print_r($account);
								
								$query = rpv_v2("SELECT m.`samname` FROM `pb_contacts` AS m WHERE m.`samname` = ! LIMIT 1", array($account['samaccountname'][0]));
								$res = db_select($query);
								if($res !== FALSE)
								{
									$query = rpv_v2("UPDATE `pb_contacts` SET `fname` = !, `lname` = !, `dep` = !, `org` = !, `pos` = !, `pint` = !, `pcell` = !, `mail` = ! WHERE `samname` = ! LIMIT 1", array(@$account['givenname'][0], @$account['sn'][0], @$account['department'][0], @$account['company'][0], @$account['title'][0], @$account['telephonenumber'][0], @$account['mobile'][0], @$account['mail'][0], @$account['samaccountname'][0]));
									$res = db_put($query);
								}
								else
								{
									$query = rpv_v2("INSERT INTO `pb_contacts` (`samname`, `fname`, `lname`, `dep`, `org`, `pos`, `pint`, `pcell`, `mail`) VALUES (!, !, !, !, !, !, !, !, !)", array(@$account['samaccountname'][0], @$account['givenname'][0], @$account['sn'][0], @$account['department'][0], @$account['company'][0], @$account['title'][0], @$account['telephonenumber'][0], @$account['mobile'][0], @$account['mail'][0]));
									$res = db_put($query);
								}
							}
						}
						db_disconnect();
					}
				}
			}
			exit;
		}
	}

	db_connect();
	$query = rpv_v2("SELECT m.`id`, m.`samname`, m.`fname`, m.`lname`, m.`dep`, m.`org`, m.`pos`, m.`pint`, m.`pcell`, m.`mail` FROM `pb_contacts` AS m WHERE m.`visible` = 0 ORDER BY m.`lname`, m.`fname`", array());
	$res = db_select($query);
	db_disconnect();
	if($res === FALSE)
	{
	}
	include('templ/tpl.main.php');
	//include('templ/tpl.debug.php');
