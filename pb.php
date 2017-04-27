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

function php_mailer($to, $name, $subject, $html, $plain)
{
	require_once 'libs/PHPMailer/PHPMailerAutoload.php';

	$mail = new PHPMailer;

	$mail->isSMTP();
	$mail->Host = MAIL_HOST;
	$mail->SMTPAuth = MAIL_AUTH;
	if(MAIL_AUTH)
	{
		$mail->Username = MAIL_LOGIN;
		$mail->Password = MAIL_PASSWD;
	}

	$mail->SMTPSecure = MAIL_SECURE;
	$mail->Port = MAIL_PORT;

	$mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
	$mail->addAddress($to, $name);
	//$mail->addReplyTo('helpdesk@example.com', 'Information');

	$mail->isHTML(true);

	$mail->Subject = $subject;
	$mail->Body    = $html;
	$mail->AltBody = $plain;

	return $mail->send();
}


	session_name("ZID");
	session_start();
	error_reporting(E_ALL);
	define("Z_PROTECTED", "YES");

	$self = $_SERVER['PHP_SELF'];

	$uid = 0;
	if(isset($_SESSION['uid']))
	{
		$uid = $_SESSION['uid'];
	}

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
	
	if($action == "message")
	{
		switch($id)
		{
			case 1:
				$error_msg = "Registration is complete. Wait for the administrator to activate your account.";
				break;
			default:
				$error_msg = "Unknown error";
				break;
		}
		
		include('templ/tpl.message.php');
		exit;
	}

	$db = new MySQLDB();
	$db->connect();
		
	$uid = 0;
	if(isset($_SESSION['uid']))
	{
		$uid = $_SESSION['uid'];
	}

	if(empty($uid))
	{
		if(!empty($_COOKIE['zh']) && !empty($_COOKIE['zl']))
		{
			if($db->select(rpv("SELECT m.`id` FROM zxs_users AS m WHERE m.`login` = ! AND m.`sid` IS NOT NULL AND m.`sid` = ! AND m.`deleted` = 0 LIMIT 1", $_COOKIE['zl'], $_COOKIE['zh'])))
			{
				$_SESSION['uid'] = $db->data[0][0];
				$uid = $_SESSION['uid'];
				setcookie("zh", $_COOKIE['zh'], time()+2592000, '/');
				setcookie("zl", $_COOKIE['zl'], time()+2592000, '/');
			}
		}
	}

	if(empty($uid))
	{
		switch($action)
		{
			case 'logon':
			{
				if(empty($_POST['login']) || empty($_POST['passwd']))
				{
					$error_msg = "Неверное имя пользователя или пароль!";
					include('templ/tpl.login.php');
					exit;
				}

				if(!$db->select(rpv("SELECT m.`id` FROM pb_users AS m WHERE m.`login` = ! AND m.`passwd` = PASSWORD(!) AND m.`deleted` = 0 LIMIT 1", @$_POST['login'], @$_POST['passwd'])))
				{
					//$db->put(rpv("INSERT INTO `zxs_log` (`date`, `uid`, `type`, `p1`, `ip`) VALUES (NOW(), #, #, #, !)", 0, LOG_LOGIN_FAILED, 0, $ip));
					$error_msg = "Неверное имя пользователя или пароль!";
					include('templ/tpl.login.php');
					exit;
				}

				$_SESSION['uid'] = $db->data[0][0];
				$uid = $_SESSION['uid'];

				$sid = uniqid();
				setcookie("zh", $sid, time()+2592000, '/');
				setcookie("zl", @$_POST['login'], time()+2592000, '/');

				$db->put(rpv("UPDATE pb_users SET `sid` = ! WHERE `id` = # LIMIT 1", $sid, $uid));
				//$db->put(rpv("INSERT INTO `zxs_log` (`date`, `uid`, `type`, `p1`, `ip`) VALUES (NOW(), #, #, #, !)", $uid, LOG_LOGIN, 0, $ip));

				header('Location: '.$self);
				exit;
			}
			case 'register': // show registartion form
			{
				include('templ/tpl.register.php');
				exit;
			}
			case 'reg': // register new account
			{
				if(empty($_POST['login']) || empty($_POST['passwd']) || empty($_POST['mail']) || !preg_match('/'.ALLOW_MAILS.'/i', $_POST['mail']))
				{
					$error_msg = "Указаны неверные данные!";
					include('templ/tpl.register.php');
					exit;
				}

				if($db->select(rpv("SELECT m.`id` FROM pb_users AS m WHERE m.`login`= ! OR m.`mail` = ! LIMIT 1", @$_POST['login'], @$_POST['mail'])))
				{
					$res = $db->data;
					$error_msg = "Пользователь существует!";
					include('templ/tpl.register.php');
					exit;
				}
				$db->put(rpv("INSERT INTO pb_users (login, passwd, mail, deleted) VALUES (!, PASSWORD(!), !, 1)", @$_POST['login'], @$_POST['passwd'], @$_POST['mail']));
				$uid = $db->last_id();

				// send mail to admin for accept registration
				if(!php_mailer(
					MAIL_ADMIN, MAIL_ADMIN_NAME,
					'Accept new registration',
					'Hello, Admin!<br /><br />New user wish to register.<br />Login: <b>'.@$_POST['login'].'</b><br />E-Mail: <b>'.@$_POST['mail'].'</b><br/><br/>Accept registration: <a href="'.$self.'?action=activate&amp;login='.@$_POST['login'].'&amp;id='.$uid.'">Accept</a>',
					'Hello, Admin! New user wish to register. Accept registration: '.$self.'?action=activate&amp;login='.@$_POST['login'].'&amp;id='.$uid
				))
				{
					$error_msg = 'Mailer Error: ' . $mail->ErrorInfo;
					include('templ/tpl.register.php');
					exit;
				}

				header("Location: $self?action=message&id=1");
				exit;
			}
			case 'login': // activate account after registartion
			{
				include('templ/tpl.login.php'); // show login form
				exit;
			}
		}
	}

	switch($action)
	{
		case 'logoff':
		{
			$db->put(rpv("UPDATE pb_users SET `sid` = NULL WHERE `id` = # LIMIT 1", $uid));
			$_SESSION['uid'] = 0;
			$uid = $_SESSION['uid'];
			setcookie("zh", NULL, time()-60, '/');
			setcookie("zl", NULL, time()-60, '/');

			break;
		}
		case 'activate': // activate account after registartion
		{
			if(empty($_GET['login']) || empty($id))
			{
				$error_msg = "Неверные данные активации!";
				include('templ/tpl.error.php');
				exit;
			}

			$db->put(rpv("UPDATE pb_users SET `deleted` = 0 WHERE `login` = ! AND `id` = #", @$_GET['login'], $id));
			//$db->put(rpv("INSERT INTO `zxs_log` (`date`, `uid`, `type`, `p1`, `ip`) VALUES (NOW(), #, #, #, !)", 0, LOG_LOGIN_ACTIVATE, $id, $ip));

			if($db->select(rpv("SELECT m.`id`, m.`mail` FROM pb_users AS m WHERE m.`login`= ! AND m.`id` = # LIMIT 1", @$_GET['login'], $id)))
			{
				if(!php_mailer(
					$db->data[0][1], @$_GET['login'],
					'Registration accepted',
					'Hello!<br /><br />You account activated.<br /><br/><a href="'.$self.'">Login</a>',
					'Hello! You account activated.'
				))
				{
					$error_msg = 'Mailer Error: ' . $mail->ErrorInfo;
					include('templ/tpl.error.php');
					exit;
				}
			}
			break;
		}
		case 'login': // activate account after registartion
		{
			include('templ/tpl.login.php'); // show login form
			exit;
		}
		case 'sync':
		{
			if(!$uid) break;
			header("Content-Type: text/plain; charset=utf-8");
			$ldap = ldap_connect(LDAP_HOST, LDAP_PORT);
			if($ldap)
			{
				ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
				ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
				if(ldap_bind($ldap, LDAP_USER, LDAP_PASSWD))
				{
					//$db->connect();
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

									print_r($account);

									// *********************************************************
									$s_mime = '';
									if(isset($account['thumbnailphoto'][0]))
									{
										$s_mime = $finfo->buffer($account['thumbnailphoto'][0]);
										echo "MIME: ".$mime . "\n";
									}
									
									$s_login = @$account['samaccountname'][0];
									$s_first_name = @$account['givenname'][0];
									$s_last_name = @$account['sn'][0];
									$s_department = @$account['department'][0];
									$s_organization = @$account['company'][0];
									$s_position = @$account['title'][0];
									$s_phone_internal = @$account['telephonenumber'][0];
									$s_phone_mobile = @$account['mobile'][0];
									$s_mail = @$account['mail'][0];
									$s_photo = @$account['thumbnailphoto'][0];
									
									// *********************************************************

									if($db->select(rpv("SELECT m.`samname` FROM `pb_contacts` AS m WHERE m.`samname` = ! LIMIT 1", $s_login)))
									{
										$db->put(rpv("UPDATE `pb_contacts` SET `fname` = !, `lname` = !, `dep` = !, `org` = !, `pos` = !, `pint` = !, `pcell` = !, `mail` = !, `mime` = !, `photo` = ! WHERE `samname` = ! LIMIT 1", $s_first_name, $s_last_name, $s_department, $s_organization, $s_position, $s_phone_internal, $s_phone_mobile, $s_mail, $s_mime, base64_encode($s_photo), $s_login));
									}
									else
									{
										$db->put(rpv("INSERT INTO `pb_contacts` (`samname`, `fname`, `lname`, `dep`, `org`, `pos`, `pint`, `pcell`, `mail`, `mime`, `photo`, `visible`) VALUES (!, !, !, !, !, !, !, !, !, !, !, 1)", $s_login, $s_first_name, $s_last_name, $s_department, $s_organization, $s_position, $s_phone_internal, $s_phone_mobile, $s_mail, $s_mime, base64_encode($s_photo)));
									}
									//echo "\r\n".$db->get_last_error()."\r\n";
								}
							}
							ldap_control_paged_result_response($ldap, $sr, $cookie);
							ldap_free_result($sr);
						}

					}
					while($cookie !== null && $cookie != '');

					//$db->disconnect();
					ldap_unbind($ldap);
				}
			}
		}
		exit;
		case 'export':
		{
			header("Content-Type: text/plain; charset=utf-8");
			//$db->connect();
			$db->select(rpv("SELECT m.`id`, m.`samname`, m.`fname`, m.`lname`, m.`dep`, m.`org`, m.`pos`, m.`pint`, m.`pcell`, m.`mail` FROM `pb_contacts` AS m WHERE m.`visible` = 1 ORDER BY m.`lname`, m.`fname`"));
			
			include('templ/tpl.export.php');
						
			//$db->disconnect();
		}
		exit;
		case 'hide':
		{
			if(!$uid)
			{
				echo '{"result": 1, "message": "Please, login"}';
				exit;
			}
			header("Content-Type: text/plain; charset=utf-8");
			//$db->connect();
			$db->put(rpv("UPDATE `pb_contacts` SET `visible` = 0 WHERE `id` = # LIMIT 1", $id));
			//$db->disconnect();
			echo '{"result": 0, "message": "Successful hide (ID '.$id.')"}';
		}
		exit;
		case 'show':
		{
			if(!$uid)
			{
				echo '{"result": 1, "message": "Please, login"}';
				exit;
			}
			header("Content-Type: text/plain; charset=utf-8");
			//$db->connect();
			$db->put(rpv("UPDATE `pb_contacts` SET `visible` = 1 WHERE `id` = # LIMIT 1", $id));
			//$db->disconnect();
			echo '{"result": 0, "message": "Successful show (ID '.$id.')"}';
		}
		exit;
		case 'setlocation':
		{
			if(!$uid)
			{
				echo '{"result": 1, "message": "Please, login"}';
				exit;
			}
			header("Content-Type: text/plain; charset=utf-8");

			$db->put(rpv("UPDATE `pb_contacts` SET `map` = #, `x` = #, `y` = # WHERE `id` = # LIMIT 1", @$_POST['map'], @$_POST['x'], @$_POST['y'], $id));

			echo '{"result": 0, "message": "Location set (ID '.$id.')"}';
		}
		exit;
		case 'map':
		{
			header("Content-Type: text/html; charset=utf-8");
			//$db->connect();
			$db->select(rpv("SELECT m.`id`, m.`samname`, m.`fname`, m.`lname`, m.`dep`, m.`org`, m.`pos`, m.`pint`, m.`pcell`, m.`mail`, m.`mime`, m.`photo`, m.`map`, m.`x`, m.`y`, m.`visible` FROM `pb_contacts` AS m WHERE m.`visible` = 1 AND m.`map` = # ORDER BY m.`lname`, m.`fname`", $id));

			include('templ/tpl.map.php');

			//$db->disconnect();
		}
		exit;
	}

	header("Content-Type: text/html; charset=utf-8");

	//$db->connect();
	$db->select(rpv("SELECT m.`id`, m.`samname`, m.`fname`, m.`lname`, m.`dep`, m.`org`, m.`pos`, m.`pint`, m.`pcell`, m.`mail`, m.`mime`, m.`photo`, m.`map`, m.`x`, m.`y`, m.`visible` FROM `pb_contacts` AS m ? ORDER BY m.`lname`, m.`fname`", $uid?'':'WHERE m.`visible` = 1'));
	//$db->disconnect();

	include('templ/tpl.main.php');
	//include('templ/tpl.debug.php');
