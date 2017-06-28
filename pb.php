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
			if($db->select(rpv("SELECT m.`id` FROM @users AS m WHERE m.`login` = ! AND m.`sid` IS NOT NULL AND m.`sid` = ! AND m.`deleted` = 0 LIMIT 1", $_COOKIE['zl'], $_COOKIE['zh'])))
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

				if(!$db->select(rpv("SELECT m.`id` FROM @users AS m WHERE m.`login` = ! AND m.`passwd` = PASSWORD(!) AND m.`deleted` = 0 LIMIT 1", @$_POST['login'], @$_POST['passwd'])))
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

				$db->put(rpv("UPDATE @users SET `sid` = ! WHERE `id` = # LIMIT 1", $sid, $uid));
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

				if($db->select(rpv("SELECT m.`id` FROM @users AS m WHERE m.`login`= ! OR m.`mail` = ! LIMIT 1", @$_POST['login'], @$_POST['mail'])))
				{
					$res = $db->data;
					$error_msg = "Пользователь существует!";
					include('templ/tpl.register.php');
					exit;
				}
				$db->put(rpv("INSERT INTO @users (login, passwd, mail, deleted) VALUES (!, PASSWORD(!), !, 1)", @$_POST['login'], @$_POST['passwd'], @$_POST['mail']));
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
			$db->put(rpv("UPDATE @users SET `sid` = NULL WHERE `id` = # LIMIT 1", $uid));
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

			$db->put(rpv("UPDATE @users SET `deleted` = 0 WHERE `login` = ! AND `id` = #", @$_GET['login'], $id));
			//$db->put(rpv("INSERT INTO `zxs_log` (`date`, `uid`, `type`, `p1`, `ip`) VALUES (NOW(), #, #, #, !)", 0, LOG_LOGIN_ACTIVATE, $id, $ip));

			if($db->select(rpv("SELECT m.`id`, m.`mail` FROM @users AS m WHERE m.`login`= ! AND m.`id` = # LIMIT 1", @$_GET['login'], $id)))
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
			//header("Content-Type: text/plain; charset=utf-8");
			header("Content-Type: text/html; charset=utf-8");

			$ldap = ldap_connect(LDAP_HOST, LDAP_PORT);
			if($ldap)
			{
				ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
				ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
				if(ldap_bind($ldap, LDAP_USER, LDAP_PASSWD))
				{
					$upload_dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'photos';

					$data = array();
					$count_updated = 0;
					$count_added = 0;
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
								if(!empty($account['samaccountname'][0]) && !empty($account['givenname'][0]) && !empty($account['sn'][0]))
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

									//print_r($account);

									// *********************************************************

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
									$s_visible = ((bool)(@$account['useraccountcontrol'][0] & 0x2))?0:1;

									// *********************************************************

									if($db->select(rpv("SELECT m.`id`, m.`samname` FROM `@contacts` AS m WHERE m.`samname` = ! LIMIT 1", $s_login)))
									{
										$id = $db->data[0][0];
										$db->put(rpv("UPDATE `@contacts` SET `fname` = !, `lname` = !, `dep` = !, `org` = !, `pos` = !, `pint` = !, `pcell` = !, `mail` = !, `photo` = # WHERE `samname` = ! LIMIT 1", $s_first_name, $s_last_name, $s_department, $s_organization, $s_position, $s_phone_internal, $s_phone_mobile, $s_mail, isset($account['thumbnailphoto'][0])?1:0, $s_login));
										$count_updated++;
									}
									else
									{
										$db->put(rpv("INSERT INTO `@contacts` (`samname`, `fname`, `lname`, `dep`, `org`, `pos`, `pint`, `pcell`, `mail`, `photo`, `visible`) VALUES (!, !, !, !, !, !, !, !, !, #, 1)", $s_login, $s_first_name, $s_last_name, $s_department, $s_organization, $s_position, $s_phone_internal, $s_phone_mobile, $s_mail, isset($account['thumbnailphoto'][0])?1:0));
										$id = $db->last_id();
										$count_added++;

										$data[] = array(
											$id,
											$s_login,
											$s_first_name,
											$s_last_name,
											$s_department,
											$s_organization,
											$s_position,
											$s_phone_internal,
											$s_phone_mobile,
											$s_mail,
											isset($account['thumbnailphoto'][0])?1:0,
											0,
											0,
											0,
											1
										);
									}
									//echo "\r\n".$db->get_last_error()."\r\n";

									if(isset($account['thumbnailphoto'][0]))
									{
										$w = 64;
										$h = 64;
										list($width, $height) = getimagesizefromstring($s_photo);
										$r = $w / $h;
										if($width/$height > $r)
										{
											$src_width = ceil($height*$r);
											$src_x = ceil(($width - $src_width)/2);
											$src_y = 0;
											$src_height = $height;
										}
										else
										{
											$src_height = ceil($width/$r);
											$src_y = ceil(($height - $src_height)/2);
											$src_x = 0;
											$src_width = $width;
										}
										$src = imagecreatefromstring($s_photo);
										$dst = imagecreatetruecolor($w, $h);
										imagecopyresampled($dst, $src, 0, 0, $src_x, $src_y, $w, $h, $src_width, $src_height);
										imagejpeg($dst, $upload_dir.DIRECTORY_SEPARATOR.'t'.$id.'.jpg', 100);
										imagedestroy($dst);
										imagedestroy($src);
									}
								}
							}
							ldap_control_paged_result_response($ldap, $sr, $cookie);
							ldap_free_result($sr);
						}

					}
					while($cookie !== null && $cookie != '');

					ldap_unbind($ldap);
					//echo 'Updated: '.$count_updated.', added: '.$count_added.' contacts';
					include('templ/tpl.sync.php');
				}
			}
		}
		exit;
		case 'hide_disabled':
		{
			if(!$uid) break;
			header("Content-Type: text/html; charset=utf-8");
			$ldap = ldap_connect(LDAP_HOST, LDAP_PORT);
			if($ldap)
			{
				ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
				ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
				if(ldap_bind($ldap, LDAP_USER, LDAP_PASSWD))
				{
					$data = array();
					$count_updated = 0;
					$count_added = 0;
					$cookie = '';
					do
					{
						ldap_control_paged_result($ldap, 200, true, $cookie);

						$sr = ldap_search($ldap, LDAP_BASE_DN, "(&(objectClass=person)(objectClass=user)(sAMAccountType=805306368)(userAccountControl:1.2.840.113556.1.4.803:=2))", array('samaccountname', 'useraccountcontrol'));
						if($sr)
						{
							$records = ldap_get_entries($ldap, $sr);
							foreach($records as $account)
							{
								if(!empty($account['samaccountname'][0]))
								{
									// *********************************************************

									$s_login = @$account['samaccountname'][0];
									$s_disabled = ((bool)(@$account['useraccountcontrol'][0] & 0x2))?1:0;

									// *********************************************************

									if($s_disabled && $db->select(rpv("SELECT m.`id`, m.`samname`, m.`fname`, m.`lname`, m.`dep`, m.`org`, m.`pos`, m.`pint`, m.`pcell`, m.`mail`, m.`photo`, m.`map`, m.`x`, m.`y`, m.`visible` FROM `@contacts` AS m WHERE m.`samname` = ! AND m.`visible` = 1 LIMIT 1", $s_login)))
									{
										$id = $db->data[0][0];
										$data[] = $db->data[0];
										$db->put(rpv("UPDATE `@contacts` SET `visible` = 0 WHERE `id` = # LIMIT 1", $id));
										$count_updated++;
									}
								}
							}
							ldap_control_paged_result_response($ldap, $sr, $cookie);
							ldap_free_result($sr);
						}

					}
					while($cookie !== null && $cookie != '');

					ldap_unbind($ldap);

					include('templ/tpl.sync.php');
				}
			}
		}
		exit;
		case 'export':
		{
			header("Content-Type: text/plain; charset=utf-8");
			header("Content-Disposition: attachment; filename=\"base.xml\"; filename*=utf-8''base.xml");

			$db->select(rpv("SELECT m.`id`, m.`samname`, m.`fname`, m.`lname`, m.`dep`, m.`org`, m.`pos`, m.`pint`, m.`pcell`, m.`mail` FROM `@contacts` AS m WHERE m.`visible` = 1 ORDER BY m.`lname`, m.`fname`"));

			$result = $db->data;

			include('templ/tpl.export.php');
		}
		exit;
		case 'export_selected':
		{
			header("Content-Type: text/plain; charset=utf-8");
			header("Content-Disposition: attachment; filename=\"base.xml\"; filename*=utf-8''base.xml");

			$result = array();

			if(isset($_POST['list']))
			{
				$j = 0;
				$list_safe = '';
				$list = explode(',', $_POST['list']);
				foreach($list as $id)
				{
					if($j > 0)
					{
						$list_safe .= ',';
					}

					$list_safe .= intval($id);
					$j++;
				}

				if($j > 0)
				{
					if($db->select(rpv("SELECT m.`id`, m.`samname`, m.`fname`, m.`lname`, m.`dep`, m.`org`, m.`pos`, m.`pint`, m.`pcell`, m.`mail` FROM `@contacts` AS m WHERE m.`id` IN (?) ORDER BY m.`lname`, m.`fname`", $list_safe)))
					{
						$result = $db->data;
					}
				}
			}

			include('templ/tpl.export.php');
		}
		exit;
		case 'hide':
		{
			header("Content-Type: text/plain; charset=utf-8");
			if(!$uid)
			{
				echo '{"code": 1, "message": "Please, log in"}';
				exit;
			}

			$db->put(rpv("UPDATE `@contacts` SET `visible` = 0 WHERE `id` = # LIMIT 1", $id));

			echo '{"code": 0, "message": "Successful hide (ID '.$id.')"}';
		}
		exit;
		case 'show':
		{
			header("Content-Type: text/plain; charset=utf-8");
			if(!$uid)
			{
				echo '{"code": 1, "message": "Please, log in"}';
				exit;
			}

			$db->put(rpv("UPDATE `@contacts` SET `visible` = 1 WHERE `id` = # LIMIT 1", $id));

			echo '{"code": 0, "message": "Successful show (ID '.$id.')"}';
		}
		exit;
		case 'setlocation':
		{
			header("Content-Type: text/plain; charset=utf-8");
			if(!$uid)
			{
				echo '{"code": 1, "message": "Please, log in"}';
				exit;
			}
			if(@$_POST['map'] > PB_MAPS_COUNT)
			{
				echo '{"code": 1, "message": "Invalid map identifier"}';
				exit;
			}

			$db->put(rpv("UPDATE `@contacts` SET `map` = #, `x` = #, `y` = # WHERE `id` = # LIMIT 1", @$_POST['map'], @$_POST['x'], @$_POST['y'], $id));

			echo '{"code": 0, "id": '.$id.', "map": '.json_escape(@$_POST['map']).', "x": '.json_escape(@$_POST['x']).', "y": '.json_escape(@$_POST['y']).', "message": "Location set (ID '.$id.')"}';
		}
		exit;
		case 'setphoto':
		{
			header("Content-Type: text/plain; charset=utf-8");
			if(!$uid)
			{
				echo '{"code": 1, "message": "Please, log in"}';
				exit;
			}
			if(!$id)
			{
				echo '{"code": 1, "message": "Invalid identifier"}';
				exit;
			}
			if(!file_exists(@$_FILES['photo']['tmp_name']))
			{
				echo '{"code": 1, "message": "Invalid photo"}';
				exit;
			}

			$s_photo = file_get_contents(@$_FILES['photo']['tmp_name']);
			$w = 64;
			$h = 64;
			list($width, $height) = getimagesizefromstring($s_photo);
			$r = $w / $h;
			if($width/$height > $r)
			{
				$src_width = ceil($height*$r);
				$src_x = ceil(($width - $src_width)/2);
				$src_y = 0;
				$src_height = $height;
			}
			else
			{
				$src_height = ceil($width/$r);
				$src_y = ceil(($height - $src_height)/2);
				$src_x = 0;
				$src_width = $width;
			}
			$src = imagecreatefromstring($s_photo);
			$dst = imagecreatetruecolor($w, $h);
			imagecopyresampled($dst, $src, 0, 0, $src_x, $src_y, $w, $h, $src_width, $src_height);
			imagejpeg($dst, dirname(__FILE__).DIRECTORY_SEPARATOR.'photos'.DIRECTORY_SEPARATOR.'t'.$id.'.jpg', 100);
			imagedestroy($dst);
			imagedestroy($src);

			$db->put(rpv("UPDATE `@contacts` SET `photo` = 1 WHERE `id` = # LIMIT 1", $id));

			echo '{"code": 0, "id": '.$id.', "message": "Photo set (ID '.$id.')"}';
		}
		exit;
		case 'deletephoto':
		{
			header("Content-Type: text/plain; charset=utf-8");
			if(!$uid)
			{
				echo '{"code": 1, "message": "Please, log in"}';
				exit;
			}
			if(!$id)
			{
				echo '{"code": 1, "message": "Invalid identifier"}';
				exit;
			}

			$db->put(rpv("UPDATE `@contacts` SET `photo` = 0 WHERE `id` = # LIMIT 1", $id));

			echo '{"code": 0, "id": '.$id.', "message": "Photo deleted (ID '.$id.')"}';
		}
		exit;
		case 'save':
		{
			header("Content-Type: text/plain; charset=utf-8");
			if(!$uid)
			{
				echo '{"code": 1, "message": "Please, log in"}';
				exit;
			}

			$s_first_name = @$_POST['firstname'];
			$s_last_name = @$_POST['lastname'];
			$s_department = @$_POST['department'];
			$s_organization = @$_POST['company'];
			$s_position = @$_POST['position'];
			$s_phone_internal = @$_POST['phone'];
			$s_phone_mobile = @$_POST['mobile'];
			$s_mail = @$_POST['mail'];
			$s_photo = 0;

			if(!$id)
			{
				$db->put(rpv("INSERT INTO `@contacts` (`samname`, `fname`, `lname`, `dep`, `org`, `pos`, `pint`, `pcell`, `mail`, `photo`, `visible`) VALUES ('', !, !, !, !, !, !, !, !, #, 1)", $s_first_name, $s_last_name, $s_department, $s_organization, $s_position, $s_phone_internal, $s_phone_mobile, $s_mail, $s_photo));
				$id = $db->last_id();
				echo '{"code": 0, "id": '.$id.', "message": "Added (ID '.$id.')"}';
			}
			else
			{
				$db->put(rpv("UPDATE `@contacts` SET `fname` = !, `lname` = !, `dep` = !, `org` = !, `pos` = !, `pint` = !, `pcell` = !, `mail` = !, `photo` = # WHERE `id` = # AND `samname` = '' LIMIT 1", $s_first_name, $s_last_name, $s_department, $s_organization, $s_position, $s_phone_internal, $s_phone_mobile, $s_mail, $s_photo, $id));
				echo '{"code": 0, "id": '.$id.',"message": "Updated (ID '.$id.')"}';
			}
		}
		exit;
		case 'delete':
		{
			header("Content-Type: text/plain; charset=utf-8");
			if(!$uid)
			{
				echo '{"code": 1, "message": "Please, log in"}';
				exit;
			}
			if(!$id)
			{
				echo '{"code": 1, "message": "Invalid identifier"}';
				exit;
			}

			$db->put(rpv("DELETE FROM `@contacts` WHERE `id` = # AND `samname` = '' LIMIT 1", $id));

			$filename = dirname(__FILE__).DIRECTORY_SEPARATOR.'photos'.DIRECTORY_SEPARATOR.'t'.$id.'.jpg';
			if(file_exists($filename))
			{
				unlink($filename);
			}

			echo '{"code": 0, "message": "Deleted (ID '.$id.')"}';
		}
		exit;
		case 'get':
		{
			header("Content-Type: text/plain; charset=utf-8");
			if(!$id)
			{
				echo '{"code": 1, "message": "Invalid identifier"}';
				exit;
			}

			if(!$db->select(rpv("SELECT m.`id`, m.`samname`, m.`fname`, m.`lname`, m.`dep`, m.`org`, m.`pos`, m.`pint`, m.`pcell`, m.`mail`, m.`photo`, m.`map`, m.`x`, m.`y`, m.`visible` FROM `@contacts` AS m WHERE m.`id` = # LIMIT 1", $id)))
			{
				echo '{"code": 1, "message": "DB error"}';
				exit;
			}

			echo '{"code": 0, "id": '.intval($db->data[0][0]).', "samname": "'.json_escape($db->data[0][1]).'", "firstname": "'.json_escape($db->data[0][2]).'", "lastname": "'.json_escape($db->data[0][3]).'", "department": "'.json_escape($db->data[0][4]).'", "company": "'.json_escape($db->data[0][5]).'", "position": "'.json_escape($db->data[0][6]).'", "phone": "'.json_escape($db->data[0][7]).'", "mobile": "'.json_escape($db->data[0][8]).'", "mail": "'.json_escape($db->data[0][9]).'", "photo": '.intval($db->data[0][10]).', "map": '.intval($db->data[0][11]).', "x": '.intval($db->data[0][12]).', "y": '.intval($db->data[0][13]).', "visible": '.intval($db->data[0][14]).'}';
		}
		exit;
		case 'get_acs_location':
		{
			header("Content-Type: text/plain; charset=utf-8");
			if(!$id)
			{
				echo '{"code": 1, "message": "Invalid identifier"}';
				exit;
			}

			if(!$db->select(rpv("SELECT m.`id`, m.`samname`, m.`fname`, m.`lname` FROM `@contacts` AS m WHERE m.`id` = # LIMIT 1", $id)))
			{
				echo '{"code": 1, "message": "DB error"}';
				exit;
			}

			require_once('inc.acs.php');

			echo '{"code": 0, "id": '.intval($db->data[0][0]).', "location": '.intval(get_acs_location($db->data[0][0], $db->data[0][1], $db->data[0][2], $db->data[0][3])).'}';
		}
		exit;
		case 'map':
		{
			header("Content-Type: text/html; charset=utf-8");

			$db->select(rpv("SELECT m.`id`, m.`samname`, m.`fname`, m.`lname`, m.`dep`, m.`org`, m.`pos`, m.`pint`, m.`pcell`, m.`mail`, m.`photo`, m.`map`, m.`x`, m.`y`, m.`visible` FROM `@contacts` AS m WHERE m.`visible` = 1 AND m.`map` = # ORDER BY m.`lname`, m.`fname`", $id));

			include('templ/tpl.map.php');
		}
		exit;
		case 'all':
		{
			header("Content-Type: text/html; charset=utf-8");

			$db->select(rpv("SELECT m.`id`, m.`samname`, m.`fname`, m.`lname`, m.`dep`, m.`org`, m.`pos`, m.`pint`, m.`pcell`, m.`mail`, m.`photo`, m.`map`, m.`x`, m.`y`, m.`visible` FROM `@contacts` AS m ORDER BY m.`lname`, m.`fname`"));

			include('templ/tpl.main.php');
		}
		exit;
	}

	header("Content-Type: text/html; charset=utf-8");

	$db->select(rpv("SELECT m.`id`, m.`samname`, m.`fname`, m.`lname`, m.`dep`, m.`org`, m.`pos`, m.`pint`, m.`pcell`, m.`mail`, m.`photo`, m.`map`, m.`x`, m.`y`, m.`visible` FROM `@contacts` AS m WHERE m.`visible` = 1 ORDER BY m.`lname`, m.`fname`"));

	include('templ/tpl.main.php');
	//include('templ/tpl.debug.php');
