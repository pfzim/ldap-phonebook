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

if(!defined('ROOTDIR'))
{
	define('ROOTDIR', dirname(__FILE__).DIRECTORY_SEPARATOR);
}

if(!file_exists(ROOTDIR.'inc.config.php'))
{
	header('Location: install.php');
	exit;
}

require_once(ROOTDIR.'inc.config.php');


function php_mailer($to, $name, $subject, $html, $plain)
{
	require_once(ROOTDIR.'libs/PHPMailer/PHPMailerAutoload.php');

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


	session_name('ZID');
	session_start();
	error_reporting(E_ALL);
	define('Z_PROTECTED', 'YES');

	$self = $_SERVER['PHP_SELF'];

	if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = @$_SERVER['REMOTE_ADDR'];
	}

	require_once(ROOTDIR.'language'.DIRECTORY_SEPARATOR.APP_LANGUAGE.'.php');
	require_once(ROOTDIR.'inc.db.php');
	require_once(ROOTDIR.'inc.ldap.php');
	require_once(ROOTDIR.'inc.user.php');
	require_once(ROOTDIR.'inc.utils.php');

	$action = '';
	if(isset($_GET['action']))
	{
		$action = $_GET['action'];
	}

	$id = 0;
	if(isset($_GET['id']))
	{
		$id = $_GET['id'];
	}

	if($action == 'message')
	{
		switch($id)
		{
			case 1:
				$error_msg = 'Registration is complete. Wait for the administrator to activate your account.';
				break;
			default:
				$error_msg = 'Unknown error';
				break;
		}

		include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.message.php');
		exit;
	}

	$db = new MySQLDB(DB_HOST, NULL, DB_USER, DB_PASSWD, DB_NAME, DB_CPAGE, FALSE);

	$config = array();

	if($db->select(rpv('SELECT m.`name`, m.`value` FROM @config AS m')))
	{
		foreach($db->data as &$row)
		{
			$config[$row[0]] = $row[1];
		}
	}

	if(!isset($config['db_version']) || (intval($config['db_version']) != 5))
	{
		header('Location: upgrade.php');
		exit;
	}

	$ldap = new LDAP(LDAP_URI, LDAP_USER, LDAP_PASSWD, FALSE);
	$user = new UserAuth($db, !empty(LDAP_ADMIN_GROUP_DN) ? $ldap : NULL);

	if(!$user->get_id())
	{
		header('Content-Type: text/html; charset=utf-8');
		switch($action)
		{
			case 'logon':
			{
				if(!$user->logon(@$_POST['login'], @$_POST['passwd']))
				{
					$error_msg = 'Неверное имя пользователя или пароль!';
					include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.login.php');
					exit;
				}

				if(!$user->is_member(LDAP_ADMIN_GROUP_DN))
				{
					$user->logoff();
					$error_msg = 'Access denied!';
					include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.login.php');
					exit;
				}

				header('Location: '.$self);
			}
			exit;

			case 'register': // show registartion form
			{
				include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.register.php');
			}
			exit;

			case 'reg': // register new account
			{
				if(empty($_POST['login']) || empty($_POST['passwd']) || empty($_POST['mail']) || !preg_match('/'.ALLOW_MAILS.'/i', $_POST['mail']))
				{
					$error_msg = 'Указаны неверные данные!';
					include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.register.php');
					exit;
				}

				$uid = $user->add($_POST['login'], $_POST['passwd'], $_POST['mail']);
				if(!$uid)
				{
					$error_msg = $user->get_last_error();
					include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.register.php');
					exit;
				}

				// send mail to admin for accept registration
				if(!php_mailer(
					MAIL_ADMIN, MAIL_ADMIN_NAME,
					'Accept new registration',
					'Hello, Admin!<br /><br />New user wish to register.<br />Login: <b>'.@$_POST['login'].'</b><br />E-Mail: <b>'.@$_POST['mail'].'</b><br/><br/>Accept registration: <a href="'.$self.'?action=activate&amp;login='.@$_POST['login'].'&amp;id='.$uid.'">Accept</a>',
					'Hello, Admin! New user wish to register. Accept registration: '.$self.'?action=activate&amp;login='.@$_POST['login'].'&amp;id='.$uid
				))
				{
					$error_msg = 'Mailer Error: ' . $mail->ErrorInfo;
					include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.register.php');
					exit;
				}

				header('Location: '.$self.'?action=message&id=1');
			}
			exit;

			case 'login':  // show login form
			{
				include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.login.php');
			}
			exit;
		}
	}

	switch($action)
	{
		case 'logoff':
		{
			$user->logoff();
		}
		break;

		case 'activate': // activate account after registartion
		{
			if(empty($_GET['login']) || empty($id))
			{
				$error_msg = 'Invalid activation data!';
				include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.error.php');
				exit;
			}

			if($user->activate($id, $_GET['login'], $mail))
			{
				if($mail !== FALSE)
				{
					if(!php_mailer(
						$db->data[0][1], @$_GET['login'],
						'Registration accepted',
						'Hello!<br /><br />You account activated.<br /><br/><a href="'.$self.'">Login</a>',
						'Hello! You account activated.'
					))
					{
						$error_msg = 'Mailer Error: ' . $mail->ErrorInfo;
						include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.error.php');
						exit;
					}
				}
			}
		}
		break;
		
		case 'passwd_form':  // show change password form
		{
			include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.passwd.php');
		}
		exit;

		case 'passwd_change':  // change password
		{
			if(!$user->get_id())
			{
				echo '{"code": 1, "message": "Please, log in"}';
				exit;
			}

			$result_json = array(
				'code' => 0,
				'message' => '',
				'errors' => array()
			);

			if(empty($_POST['old_passwd']) || !$user->check_password($_POST['old_passwd']))
			{
				$result_json['code'] = 1;
				$result_json['errors'][] = array('name' => 'old_passwd', 'msg' => 'Password does not match');
			}

			if(empty($_POST['passwd']))
			{
				$result_json['code'] = 1;
				$result_json['errors'][] = array('name' => 'passwd', 'msg' => 'Password cannot be empty');
			}
			else if(empty($_POST['passwd2']) || $_POST['passwd'] !== $_POST['passwd2'])
			{
				$result_json['code'] = 1;
				$result_json['errors'][] = array('name' => 'passwd2', 'msg' => 'Passwords does not match');
			}

			if($result_json['code'])
			{
				$result_json['message'] = 'Password not changed. Fix errors.';
				echo json_encode($result_json);
				exit;
			}

			if($user->change_password($_POST['passwd']))
			{
				echo '{"code": 0, "message": "Password changed"}';
			}
			else
			{
				echo '{"code": 1, "message": "Something gone wrong"}';
			}
		}
		exit;

		case 'login':  // show login form
		{
			include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.login.php'); // show login form
		}
		exit;

		case 'get_token':
		{
			header('Content-Type: text/plain; charset=utf-8');
			if(!$user->get_id())
			{
				echo '{"code": 1, "message": "Please, log in"}';
				exit;
			}

			echo '{"code": 0, "message": "You token is: '.$user->get_token().'"}';
		}
		exit;

		case 'sync':
		{
			if(!$user->get_id()) break;
			//header('Content-Type: text/plain; charset=utf-8');
			header('Content-Type: text/html; charset=utf-8');

			$upload_dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'photos';

			$data = array();
			$count_updated = 0;
			$count_added = 0;
			$cookie = '';
			do
			{
				ldap_control_paged_result($ldap->get_link(), 200, true, $cookie);

				$sr = ldap_search($ldap->get_link(), LDAP_BASE_DN, LDAP_FILTER, explode(',', LDAP_ATTRS));
				if($sr)
				{
					$records = ldap_get_entries($ldap->get_link(), $sr);
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

							if($db->select(rpv("
									SELECT
										m.`id`,
										m.`samname`
									FROM
										`@contacts` AS m
									WHERE
										m.`samname` = !
									LIMIT 1
								",
								$s_login
							)))
							{
								$id = $db->data[0][0];
								$db->put(rpv("
										UPDATE
											`@contacts`
										SET
											`fname` = !,
											`lname` = !,
											`dep` = !,
											`org` = !,
											`pos` = !,
											`pint` = !,
											`pcell` = !,
											`mail` = !,
											`photo` = #
										WHERE
											`samname` = ! LIMIT 1
									",
									$s_first_name,
									$s_last_name,
									$s_department,
									$s_organization,
									$s_position,
									$s_phone_internal,
									$s_phone_mobile,
									$s_mail,
									isset($account['thumbnailphoto'][0])?1:0,
									$s_login
								));

								$count_updated++;
							}
							else
							{
								$db->put(rpv("
										INSERT INTO `@contacts` (
											`samname`,
											`fname`,
											`lname`,
											`dep`,
											`org`,
											`pos`,
											`pint`,
											`pcell`,
											`mail`,
											`photo`,
											`visible`
										) VALUES (!, !, !, !, !, !, !, !, !, #, 1)
									",
									$s_login,
									$s_first_name,
									$s_last_name,
									$s_department,
									$s_organization,
									$s_position,
									$s_phone_internal,
									$s_phone_mobile,
									$s_mail,
									isset($account['thumbnailphoto'][0])?1:0
								));

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
					ldap_control_paged_result_response($ldap->get_link(), $sr, $cookie);
					ldap_free_result($sr);
				}

			}
			while($cookie !== null && $cookie != '');

			//echo 'Updated: '.$count_updated.', added: '.$count_added.' contacts';
			include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.sync.php');
		}
		exit;

		case 'hide_disabled':
		{
			if(!$user->get_id()) break;
			header('Content-Type: text/html; charset=utf-8');

			$data = array();
			$count_updated = 0;
			$count_added = 0;
			$cookie = '';
			do
			{
				ldap_control_paged_result($ldap->get_link(), 200, true, $cookie);

				$sr = ldap_search($ldap->get_link(), LDAP_BASE_DN, '(&(objectCategory=person)(objectClass=user)(userAccountControl:1.2.840.113556.1.4.803:=2))', array('samaccountname', 'useraccountcontrol')); // OR (sAMAccountType=805306368)
				if($sr)
				{
					$records = ldap_get_entries($ldap->get_link(), $sr);
					foreach($records as &$account)
					{
						if(!empty($account['samaccountname'][0]))
						{
							// *********************************************************

							$s_login = @$account['samaccountname'][0];
							$s_disabled = ((bool)(@$account['useraccountcontrol'][0] & 0x2))?1:0;

							// *********************************************************

							if($s_disabled && $db->select(rpv('SELECT m.`id`, m.`samname`, m.`fname`, m.`lname`, m.`dep`, m.`org`, m.`pos`, m.`pint`, m.`pcell`, m.`mail`, m.`photo`, m.`map`, m.`x`, m.`y`, m.`visible` FROM `@contacts` AS m WHERE m.`samname` = ! AND m.`visible` = 1 LIMIT 1', $s_login)))
							{
								$id = $db->data[0][0];
								$db->data[0][14] = 0;
								$data[] = $db->data[0];
								$db->put(rpv('UPDATE `@contacts` SET `visible` = 0 WHERE `id` = # LIMIT 1', $id));
								$count_updated++;
							}
						}
					}
					ldap_control_paged_result_response($ldap->get_link(), $sr, $cookie);
					ldap_free_result($sr);
				}

			}
			while($cookie !== null && $cookie != '');

			include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.sync.php');
		}
		exit;

		case 'export':
		{
			header('Content-Type: text/plain; charset=utf-8');
			header("Content-Disposition: attachment; filename=\"base.xml\"; filename*=utf-8''base.xml");

			$db->select(rpv('SELECT m.`id`, m.`samname`, m.`fname`, m.`lname`, m.`dep`, m.`org`, m.`pos`, m.`pint`, m.`pcell`, m.`mail` FROM `@contacts` AS m WHERE m.`visible` = 1 ORDER BY m.`lname`, m.`fname`'));

			$result = $db->data;

			include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.export.php');
		}
		exit;

		case 'export_xml':
		{
			header('Content-Type: text/plain; charset=utf-8');
			header("Content-Disposition: attachment; filename=\"base.xml\"; filename*=utf-8''base.xml");

			$db->select_assoc_ex($result, rpv('SELECT * FROM `@contacts` AS m'));

			include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.export-all.php');
		}
		exit;

		case 'dump_db':
		{
			header('Content-Type: text/plain; charset=utf-8');
			header("Content-Disposition: attachment; filename=\"base.sql\"; filename*=utf-8''base.sql");

			echo rpv('TRUNCATE TABLE @contacts;')."\r\n";

			$db->select_assoc_ex($result, rpv('SELECT * FROM `@contacts` AS m'));

			foreach($result as &$row)
			{
				$keys = '';
				$values = '';
				foreach($row as $key => $value)
				{
					if($key != 'id')
					{
						if(!empty($keys))
						{
							$keys .= ', ';
							$values .= ', ';
						}
						$keys .= '`'.sql_escape($key).'`';
						$values .= '\''.sql_escape($value).'\'';
					}
				}

				echo rpv('INSERT INTO @contacts (?) VALUES (?);', $keys, $values)."\r\n";
			}
		}
		exit;

		case 'import_xml':
		{
			header('Content-Type: text/plain; charset=utf-8');

			if(!file_exists(@$_FILES['file']['tmp_name']))
			{
				echo '{"code": 1, "message": "Invalid XML"}';
				exit;
			}

			$result_ok = 0;
			$result_fail = 0;

			$xml = simplexml_load_file(@$_FILES['file']['tmp_name']);
			if($xml === FALSE)
			{
				echo '{"code": 1, "message": "XML load and parse failed"}';
				exit;
			}

			if(!$db->put(rpv('TRUNCATE TABLE @contacts')))
			{
				echo '{"code": 1, "message": "Truncate table failed"}';
				exit;
			}

			foreach($xml->children() as $contact)
			{
				$keys = '';
				$values = '';
				foreach($contact->children() as $key => $value)
				{
					if($key != 'id')
					{
						if(!empty($keys))
						{
							$keys .= ', ';
							$values .= ', ';
						}
						$keys .= '`'.sql_escape($key).'`';
						$values .= '\''.sql_escape($value).'\'';
					}
				}

				if($db->put(rpv('INSERT INTO @contacts (?) VALUES (?)', $keys, $values)))
				{
					$result_ok++;
				}
				else
				{
					$result_fail++;
				}
			}

			echo '{"code": 0, "ok": '.$result_ok.', "fail": '.$result_fail.', "message": "XML imported (OK: '.$result_ok.', FAIL: '.$result_fail.')"}';
		}
		exit;

		case 'export_selected':
		{
			header('Content-Type: text/plain; charset=utf-8');
			header("Content-Disposition: attachment; filename=\"base.xml\"; filename*=utf-8''base.xml");

			$result = array();

			if(isset($_POST['list']))
			{
				$j = 0;
				$list_safe = '';
				$list = explode(',', $_POST['list']);
				foreach($list as &$id)
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
					if($db->select(rpv('SELECT m.`id`, m.`samname`, m.`fname`, m.`lname`, m.`dep`, m.`org`, m.`pos`, m.`pint`, m.`pcell`, m.`mail` FROM `@contacts` AS m WHERE m.`id` IN (?) ORDER BY m.`lname`, m.`fname`', $list_safe)))
					{
						$result = $db->data;
					}
				}
			}

			include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.export.php');
		}
		exit;

		case 'hide':
		{
			header('Content-Type: text/plain; charset=utf-8');
			if(!$user->get_id())
			{
				echo '{"code": 1, "message": "Please, log in"}';
				exit;
			}

			$db->put(rpv('UPDATE `@contacts` SET `visible` = 0 WHERE `id` = # LIMIT 1', $id));

			echo '{"code": 0, "message": "Successful hide (ID '.$id.')"}';
		}
		exit;

		case 'show':
		{
			header('Content-Type: text/plain; charset=utf-8');
			if(!$user->get_id())
			{
				echo '{"code": 1, "message": "Please, log in"}';
				exit;
			}

			$db->put(rpv('UPDATE `@contacts` SET `visible` = 1 WHERE `id` = # LIMIT 1', $id));

			echo '{"code": 0, "message": "Successful show (ID '.$id.')"}';
		}
		exit;

		case 'setlocation':
		{
			header('Content-Type: text/plain; charset=utf-8');
			if(!$user->get_id())
			{
				echo '{"code": 1, "message": "Please, log in"}';
				exit;
			}
			if(@$_POST['map'] > PB_MAPS_COUNT)
			{
				echo '{"code": 1, "message": "Invalid map identifier"}';
				exit;
			}

			$db->put(rpv('UPDATE `@contacts` SET `map` = #, `x` = #, `y` = # WHERE `id` = # LIMIT 1', @$_POST['map'], @$_POST['x'], @$_POST['y'], $id));

			echo '{"code": 0, "id": '.$id.', "map": '.json_escape(@$_POST['map']).', "x": '.json_escape(@$_POST['x']).', "y": '.json_escape(@$_POST['y']).', "message": "Location set (ID '.$id.')"}';
		}
		exit;

		case 'setphoto':
		{
			header('Content-Type: text/plain; charset=utf-8');
			if(!$user->get_id())
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

			$db->put(rpv('UPDATE `@contacts` SET `photo` = 1 WHERE `id` = # LIMIT 1', $id));

			echo '{"code": 0, "id": '.$id.', "message": "Photo set (ID '.$id.')"}';
		}
		exit;

		case 'deletephoto':
		{
			header('Content-Type: text/plain; charset=utf-8');
			if(!$user->get_id())
			{
				echo '{"code": 1, "message": "Please, log in"}';
				exit;
			}
			if(!$id)
			{
				echo '{"code": 1, "message": "Invalid identifier"}';
				exit;
			}

			$db->put(rpv('UPDATE `@contacts` SET `photo` = 0 WHERE `id` = # LIMIT 1', $id));

			echo '{"code": 0, "id": '.$id.', "message": "Photo deleted (ID '.$id.')"}';
		}
		exit;

		case 'save':
		{
			header('Content-Type: text/plain; charset=utf-8');
			if(!$user->get_id())
			{
				echo '{"code": 1, "message": "Please, log in"}';
				exit;
			}

			$result_json = array(
				'code' => 0,
				'message' => '',
				'errors' => array()
			);

			$s_id 				= intval(@$_POST['id']);
			$s_first_name 		= trim(@$_POST['firstname']);
			$s_last_name 		= trim(@$_POST['lastname']);
			$s_department 		= trim(@$_POST['department']);
			$s_organization 	= trim(@$_POST['company']);
			$s_position 		= trim(@$_POST['position']);
			$s_phone_internal 	= trim(@$_POST['phone']);
			$s_phone_city 		= trim(@$_POST['phonecity']);
			$s_phone_mobile 	= trim(@$_POST['mobile']);
			$s_mail 			= trim(@$_POST['mail']);
			$s_bday 			= trim(@$_POST['bday']);
			$s_type 			= trim(@$_POST['type']);
			$s_photo 			= 0;

			if(!empty($s_bday))
			{
				$d = explode('.', $s_bday, 3);
				$nd = intval(@$d[0]);
				$nm = intval(@$d[1]);
				$ny = intval(@$d[2]);
				$s_bday = sprintf('%04d-%02d-%02d', $ny, $nm, $nd);
				$s_bday_human = sprintf('%02d.%02d.%04d', $nd, $nm, $ny);

				if(!datecheck($nd, $nm, $ny))
				{
					$result_json['code'] = 1;
					$result_json['errors'][] = array('name' => 'bday', 'msg' => 'Date format must be DD.MM.YYYY!');
				}
			}
			else
			{
				$s_bday = '0000-00-00';
			}

			if($result_json['code'])
			{
				$result_json['message'] = 'Fill the form!';
				echo json_encode($result_json);
				exit;
			}

			if(!$s_id)
			{
				$db->put(rpv("INSERT INTO `@contacts`
								(`samname`,
								`fname`,
								`lname`,
								`dep`,
								`org`,
								`pos`,
								`pint`,
								`pcity`,
								`pcell`,
								`mail`,
								`photo`,
								`bday`,
								`type`,
								`visible`)
							VALUES ('', !, !, !, !, !, !, !, !, !, #, !, #, 1)",
								$s_first_name,
								$s_last_name,
								$s_department,
								$s_organization,
								$s_position,
								$s_phone_internal,
								$s_phone_city,
								$s_phone_mobile,
								$s_mail,
								$s_photo,
								$s_bday,
								$s_type));
				$s_id = $db->last_id();
				echo '{"code": 0, "id": '.$s_id.', "message": "Added (ID '.$s_id.')"}';
			} else {
				$db->put(rpv("UPDATE `@contacts`
							SET `fname` = !,
								`lname` = !,
								`dep` = !,
								`org` = !,
								`pos` = !,
								`pint` = !,
								`pcity` = !,
								`pcell` = !,
								`mail` = !,
								`photo` = #,
								`bday` = !,
								`type` = #
							WHERE `id` = # AND `samname` = '' LIMIT 1",
							$s_first_name,
							$s_last_name,
							$s_department,
							$s_organization,
							$s_position,
							$s_phone_internal,
							$s_phone_city,
							$s_phone_mobile,
							$s_mail,
							$s_photo,
							$s_bday,
							$s_type,
							$s_id));
				echo '{"code": 0, "id": '.$s_id.',"message": "Updated (ID '.$s_id.')"}';
			}
		}
		exit;

		case 'delete':
		{
			header('Content-Type: text/plain; charset=utf-8');
			if(!$user->get_id())
			{
				echo '{"code": 1, "message": "Please, log in"}';
				exit;
			}
			if(!$id)
			{
				echo '{"code": 1, "message": "Invalid identifier"}';
				exit;
			}

			$db->put(rpv('DELETE FROM `@contacts` WHERE `id` = # AND `samname` = \'\' LIMIT 1', $id));

			$filename = dirname(__FILE__).DIRECTORY_SEPARATOR.'photos'.DIRECTORY_SEPARATOR.'t'.$id.'.jpg';
			if(file_exists($filename))
			{
				unlink($filename);
			}

			echo '{"code": 0, "message": "Deleted (ID '.$id.')"}';
		}
		exit;

		case 'get_contact':
		{
			header('Content-Type: text/plain; charset=utf-8');
			if(!$id)
			{
				echo '{"code": 1, "message": "Invalid identifier"}';
				exit;
			}

			if(!$db->select_assoc(rpv("SELECT 	m.`id`,
												m.`samname`,
												m.`fname`,
												m.`lname`,
												m.`dep`,
												m.`org`,
												m.`pos`,
												m.`pint`,
												m.`pcell`,
												m.`mail`,
												m.`photo`,
												m.`map`,
												m.`x`,
												m.`y`,
												m.`visible`,
												DATE_FORMAT(m.`bday`, '%d.%m.%Y') AS create_date,
												m.`type`,
												m.`pcity`
								FROM `@contacts` AS m WHERE m.`id` = # LIMIT 1", $id)))
			{
				echo '{"code": 1, "message": "DB error"}';
				exit;
			}

			$row = $db->data[0];
			$compname = array('', '', '');

			if($db->select_ex($comps, rpv('SELECT m.`computer` FROM `@handshake` AS m WHERE m.`user` = ! ORDER BY m.`date` DESC LIMIT 3', $row['samname'])))
			{
				$i = 0;
				foreach($comps as &$comp)
				{
					$compname[$i++] = &$comp[0];
				}
			}

			/* // *** start cut here ***
			$line = '';

			$f = @fopen('\\\\server.with.logs\\logs$\\'.$db->data[0][1].'.txt', 'r');
			if($f !== false)
			{
				$cursor = -1;

				fseek($f, $cursor, SEEK_END);
				$char = fgetc($f);

				for($i = 0; $i < 3; $i++)
				{
					// Trim trailing newline chars of the file
					while ($char === "\n" || $char === "\r") {
						fseek($f, $cursor--, SEEK_END);
						$char = fgetc($f);
					}

					// Read until the start of file or first newline char
					while ($char !== false && $char !== "\n" && $char !== "\r") {
						// Prepend the new char
						$line = $char . $line;
						fseek($f, $cursor--, SEEK_END);
						$char = fgetc($f);
					}

					if(preg_match('/logged into ([^\s]+) ip \d+\.\d+\.\d+\.\d+ using/', $line, $match))
					{
						$compname[$i] = $match[1];
					}
				}
			}
			// *** end cut here *** */

			echo '{"code": 0,
					"data": {"id": '.intval($row["id"]).',
							"samname": "'.json_escape($row["samname"]).'",
							"firstname": "'.json_escape($row["fname"]).'",
							"lastname": "'.json_escape($row["lname"]).'",
							"department": "'.json_escape($row["dep"]).'",
							"company": "'.json_escape($row["org"]).'",
							"position": "'.json_escape($row["pos"]).'",
							"phone": "'.json_escape($row["pint"]).'",
							"phonecity": "'.json_escape($row["pcity"]).'",
							"mobile": "'.json_escape($row["pcell"]).'",
							"mail": "'.json_escape($row["mail"]).'",
							"photo": '.intval($row["photo"]).',
							"map": '.intval($row["map"]).',
							"x": '.intval($row["x"]).',
							"y": '.intval($row["y"]).',
							"visible": '.intval($row["visible"]).',
							"bday": "'.json_escape($row["create_date"]).'",
							"type": '.intval($row["type"]).',
							"pc": ["'.json_escape($compname[0]).'",
							"'.json_escape($compname[1]).'",
							"'.json_escape($compname[2]).'"]}}';
		}
		exit;

		case 'get_acs_location':
		{
			header('Content-Type: text/plain; charset=utf-8');
			if(!$id)
			{
				echo '{"code": 1, "message": "Invalid identifier"}';
				exit;
			}

			if(!$db->select(rpv('SELECT m.`id`, m.`samname`, m.`fname`, m.`lname` FROM `@contacts` AS m WHERE m.`id` = # LIMIT 1', $id)))
			{
				echo '{"code": 1, "message": "DB error"}';
				exit;
			}

			require_once('inc.acs.php');

			echo '{"code": 0, "id": '.intval($db->data[0][0]).', "location": '.intval(get_acs_location($db->data[0][0], $db->data[0][1], $db->data[0][2], $db->data[0][3])).'}';
		}
		exit;

		case 'hello':
		{
			header('Content-Type: text/plain; charset=utf-8');

			$s_user_name = trim(@$_POST['user']);
			$s_comp_name = trim(@$_POST['comp']);

			if(empty($s_user_name))
			{
				echo '{"code": 1, "message": "Undefined user name"}';
				exit;
			}

			$db->put(rpv('INSERT INTO `@handshake` (`user`, `date`, `computer`, `ip`) VALUES (!, NOW(), !, !)', $s_user_name, $s_comp_name, $ip));

			echo '{"code": 0, "message": "HI"}';
		}
		exit;

		case 'services':
		{
			header('Content-Type: text/html; charset=utf-8');

			include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.services.php');
		}
		exit;

		case 'handshakes':
		{
			header('Content-Type: text/html; charset=utf-8');

			$db->select_ex($handshakes, rpv('SELECT m.`id`, m.`date`, m.`user`, m.`computer`, m.`ip` FROM `@handshake` AS m ORDER BY m.`date` DESC, m.`user` LIMIT 1000'));

			include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.handshakes.php');
		}
		exit;

		case 'map':
		{
			header('Content-Type: text/html; charset=utf-8');

			$db->select(rpv('SELECT m.`id`, m.`samname`, m.`fname`, m.`lname`, m.`dep`, m.`org`, m.`pos`, m.`pint`, m.`pcell`, m.`mail`, m.`photo`, m.`map`, m.`x`, m.`y`, m.`type`, m.`visible` FROM `@contacts` AS m WHERE m.`visible` = 1 AND m.`map` = # ORDER BY m.`lname`, m.`fname`', $id));

			include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.map.php');
		}
		exit;

		case 'all':
		{
			header('Content-Type: text/html; charset=utf-8');

			$db->select_assoc_ex($birthdays, rpv('SELECT m.`id`, m.`samname`, m.`fname`, m.`lname`, m.`dep`, m.`org`, m.`pos`, m.`pint`, m.`pcell`, m.`mail`, m.`photo`, m.`map`, m.`x`, m.`y`, m.`visible`, DATE_FORMAT(m.`bday`, \'%d.%m\') FROM `@contacts` AS m WHERE m.`visible` = 1 AND ((DAY(m.`bday`) = DAY(NOW()) AND MONTH(m.`bday`) = MONTH(NOW())) OR (DAY(m.`bday`) = DAY(NOW() + INTERVAL 1 DAY) AND MONTH(m.`bday`) = MONTH(NOW() + INTERVAL 1 DAY)) OR (DAY(m.`bday`) = DAY(NOW() + INTERVAL 2 DAY) AND MONTH(m.`bday`) = MONTH(NOW() + INTERVAL 2 DAY)) OR (DAY(m.`bday`) = DAY(NOW() + INTERVAL 3 DAY) AND MONTH(m.`bday`) = MONTH(NOW() + INTERVAL 3 DAY)) OR (DAY(m.`bday`) = DAY(NOW() + INTERVAL 4 DAY) AND MONTH(m.`bday`) = MONTH(NOW() + INTERVAL 4 DAY)) OR (DAY(m.`bday`) = DAY(NOW() + INTERVAL 5 DAY) AND MONTH(m.`bday`) = MONTH(NOW() + INTERVAL 5 DAY)) OR (DAY(m.`bday`) = DAY(NOW() + INTERVAL 6 DAY) AND MONTH(m.`bday`) = MONTH(NOW() + INTERVAL 6 DAY)) OR (DAY(m.`bday`) = DAY(NOW() + INTERVAL 7 DAY) AND MONTH(m.`bday`) = MONTH(NOW() + INTERVAL 7 DAY))) ORDER BY MONTH(m.`bday`), DAY(m.`bday`), m.`lname`, m.`fname`'));
			$db->select_assoc(rpv('SELECT m.`id`, m.`samname`, m.`fname`, m.`lname`, m.`dep`, m.`org`, m.`pos`, m.`pint`, m.`pcell`, m.`mail`, m.`photo`, m.`map`, m.`x`, m.`y`, m.`visible`, m.`pcity` FROM `@contacts` AS m ORDER BY m.`lname`, m.`fname`'));

			include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.main.php');
		}
		exit;
	}

	header('Content-Type: text/html; charset=utf-8');

	$db->select_assoc_ex($birthdays, rpv("
		SELECT
			m.`id`,
			m.`samname`,
			m.`fname`,
			m.`lname`,
			DATE_FORMAT(m.`bday`, '%d.%m') AS DayMonth
		FROM
			`@contacts` AS m
		WHERE
			m.`visible` = 1
			AND MONTH(m.`bday`) = MONTH(NOW())
			AND DAY(m.`bday`) >= DAY(NOW())
			AND DAY(m.`bday`) <= DAY(NOW() + INTERVAL 7 DAY)
		ORDER BY
			MONTH(m.`bday`),
			DAY(m.`bday`),
			m.`lname`,
			m.`fname`
	"));

	$db->select_assoc(rpv("
		SELECT
			m.`id`,
			m.`samname`,
			m.`fname`,
			m.`lname`,
			m.`dep`,
			m.`org`,
			m.`pos`,
			m.`pint`,
			m.`pcell`,
			m.`mail`,
			m.`photo`,
			m.`map`,
			m.`x`,
			m.`y`,
			m.`visible`,
			m.`pcity`
		FROM
			`@contacts` AS m
		WHERE
			m.`visible` = 1
		ORDER BY
			m.`lname`,
			m.`fname`
	"));

	include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.main.php');
	//include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.debug.php');
