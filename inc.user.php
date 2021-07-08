<?php
/*
    UserAuth class - Internal or LDAP user authentication and access control
    Copyright (C) 2020 Dmitry V. Zimin

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

define('UA_DISABLED',	0x0001);  /// Ignored for LDAP user
define('UA_LDAP',		0x0002);  /// Is a LDAP user (overwise is internal user)

class UserAuth
{
	private $uid = 0;                    /// User ID

	private $loaded = FALSE;
	private $login = NULL;				/// sAMAccountName, zl cookie
	private $token = NULL;				/// zh cookie
	private $flags = 0;

	private $db = NULL;
	private $ldap = NULL;

	private $rights = array();           // $rights[$object_id] = $bit_flags_permissions

	private $error_msg = '';
	private $rise_exception = FALSE;

	function __construct($db, $ldap = NULL, $rise_exception = FALSE)
	{
		$this->db = $db;
		$this->ldap = $ldap;
		$this->rights = array();
		$this->loaded = FALSE;

		if(empty($_SESSION['uid']))
		{
			if(!empty($_COOKIE['zh']) && !empty($_COOKIE['zl']))
			{
				if($this->db->select_ex($user_data, rpv("
					SELECT
						m.`id`,
						m.`flags`,
						m.`login`,
						m.`sid`
					FROM
						@users AS m
					WHERE
						m.`login` = !
						AND m.`sid` IS NOT NULL
						AND m.`sid` = !
						AND (m.`flags` & 0x0001) = 0
					LIMIT 1
				", $_COOKIE['zl'], $_COOKIE['zh'])))
				{
					$this->loaded = TRUE;
					$_SESSION['uid'] = $user_data[0][0];
					$this->uid = $_SESSION['uid'];
					$this->flags = intval($user_data[0][1]);
					$this->login = $user_data[0][2];
					$this->token = $user_data[0][3];

					// Extend cookie life time
					setcookie('zh', $this->token, time() + 2592000, '/');
					setcookie('zl', $this->login, time() + 2592000, '/');
				}
			}
		}
		else
		{
			$this->uid = intval($_SESSION['uid']);

			/*
			// preload user info
		
			if($this->db->select_ex($user_data, rpv("
				SELECT
					m.`id`,
					m.`ldap`,
					m.`login`,
					m.`sid`
				FROM
					@users AS m
				WHERE
					m.`id` = #
					AND (m.`flags` & 0x0001) = 0
				LIMIT 1
			", $_SESSION['uid'])))
			{
				$this->loaded = TRUE;
				$this->uid = $user_data[0][0];
				$this->flags = intval($user_data[0][1]);
				$this->login = $user_data[0][2];
				$this->token = $user_data[0][3];
			}
			*/
		}
	}

	public function logon($login, $passwd)
	{
		if(empty($login) || empty($passwd))
		{
			$this->error('Empty login or password!');
			return FALSE;
		}

		if(strpbrk($login, '\\@'))  // LDAP authorization method
		{
			if(!$this->ldap)
			{
				$this->error('LDAP class not initialized!');
				return FALSE;
			}

			if(strpos($login, '\\'))
			{
				list($domain, $sam_account_name) = explode('\\', $login, 2);
			}
			else if(strpos($login, '@'))
			{
				list($sam_account_name, $domain) = explode('@', $login, 2);
			}

			if(!$this->ldap->reset_user($login, $passwd, TRUE))
			{
				$this->error($this->ldap->get_last_error());
				return FALSE;
			}

			if($this->db->select_ex($user_data, rpv("
					SELECT
						m.`id`,
						m.`login`,
						m.`flags`,
						m.`sid`,
						m.`passwd`
					FROM
						`@users` AS m
					WHERE
						m.`login` = !
						AND (m.`flags` & (0x0002)) = 0x0002
					LIMIT 1
				", $sam_account_name
			)))
			{
				if(!empty($user_data[0][4]))
				{
					//$this->error('Неверное имя пользователя или пароль!');
					return FALSE;
				}

				$_SESSION['uid'] = $user_data[0][0];
				$this->login = $user_data[0][1];
				$this->flags = intval($user_data[0][2]);
				$this->token = $user_data[0][3];
			}
			else // add new LDAP user
			{
				$this->token = uniqid();
				$this->login = $sam_account_name;
				$this->flags = UA_LDAP;
				$this->db->put(rpv('INSERT INTO @users (login, passwd, mail, sid, flags) VALUES (!, \'\', !, !, #)', $this->login, @$records[0]['mail'][0], $this->token, $this->flags));
				$_SESSION['uid'] = $this->db->last_id();
			}
		}
		else  // internal authorization method
		{
			if(!$this->db->select_ex($user_data, rpv("
					SELECT
						m.`id`,
						m.`login`,
						m.`flags`,
						m.`sid`
					FROM
						@users AS m
					WHERE
						m.`login` = !
						AND m.`passwd` = MD5(!)
						AND (m.`flags` & (0x0001 | 0x0002)) = 0x0000
					LIMIT 1
				", $login, $passwd
			)))
			{
				//$this->db->put(rpv('INSERT INTO `@log` (`date`, `uid`, `type`, `p1`, `ip`) VALUES (NOW(), #, #, #, !)', 0, LOG_LOGIN_FAILED, 0, $ip));
				return FALSE;
			}

			$_SESSION['uid'] = $user_data[0][0];
			$this->login = $user_data[0][1];
			$this->flags = intval($user_data[0][2]);
			$this->token = $user_data[0][3];
		}

		$this->loaded = TRUE;
		$this->uid = $_SESSION['uid'];

		if(empty($this->token))
		{
			$this->token = uniqid();
			$this->db->put(rpv('UPDATE @users SET `sid` = ! WHERE `id` = # LIMIT 1', $this->token, $this->uid));
		}

		setcookie('zh', $this->token, time() + 2592000, '/');
		setcookie('zl', $this->login, time() + 2592000, '/');

		//$this->db->put(rpv('UPDATE @users SET `sid` = ! WHERE `id` = # LIMIT 1', $this->token, $this->uid));
		
		//$this->db->put(rpv('INSERT INTO `@log` (`date`, `uid`, `type`, `p1`, `ip`) VALUES (NOW(), #, #, #, !)', $this->uid, LOG_LOGIN, 0, $ip));

		return TRUE;
	}

	public function logoff()
	{
		$_SESSION['uid'] = 0;
		setcookie('zh', NULL, time() - 60, '/');
		setcookie('zl', NULL, time() - 60, '/');

		$this->loaded = FALSE;
		$this->db->put(rpv('UPDATE @users SET `sid` = NULL WHERE `id` = # LIMIT 1', $this->uid));
		$this->uid = 0;
		$this->flags = 0;
		$this->login = NULL;
		$this->token = NULL;
	}

	public function add($login, $passwd, $mail)
	{
		if($this->db->select(rpv('SELECT u.`id` FROM @users AS u WHERE u.`login`= ! OR u.`mail` = ! LIMIT 1', $login, $mail)))
		{
			$this->error('User already exist!');
			return 0;
		}

		if(!$this->db->put(rpv('INSERT INTO @users (login, passwd, mail, flags) VALUES (!, MD5(!), !, 0x0001)', $login, $passwd, $mail)))
		{
			$this->error($this->db->get_last_error());
			return 0;
		}

		return $this->db->last_id();
	}

	public function change_password($passwd)
	{
		if($this->uid && !$this->is_ldap_user())  // Only internal user can change self password
		{
			if($this->db->put(rpv('UPDATE `@users` SET `passwd` = MD5(!) WHERE `id` = # AND (`flags` & 0x0002) = 0x0000 LIMIT 1', $passwd, $this->uid)))
			{
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	public function check_password($passwd)
	{
		if($this->uid && !$this->is_ldap_user())  // Only internal user can change self password
		{
			if($this->db->select_ex($user, rpv('SELECT u.`id` FROM `@users` AS u WHERE u.`id` = # AND u.`passwd` = MD5(!) AND (u.`flags` & 0x0002) = 0x0000 LIMIT 1', $this->uid, $passwd)))
			{
				if(intval($user[0][0]) == $this->uid)
				{
					return TRUE;
				}
			}
		}

		return FALSE;
	}
	
	/**
	 *  \brief Activate other user
	 *
	 *  \param [in] $id User ID
	 *  \param [in] $login User login
	 *  \param [out] $mail Activated user mail address for send notification
	 *  \return true - if activated successfully
	 */

	public function activate($id, $login, &$mail)
	{
		if($this->uid && !$this->is_ldap_user())  // Only internal user can activate
		{
			if($this->db->put(rpv('UPDATE @users SET `flags` = (`flags` & ~0x0001) WHERE `login` = ! AND `id` = #', $login, $id)))
			{
				//$this->db->put(rpv('INSERT INTO `@log` (`date`, `uid`, `type`, `p1`, `ip`) VALUES (NOW(), #, #, #, !)', 0, LOG_LOGIN_ACTIVATE, $id, $ip));
				$mail = FALSE;
				if($this->db->select_ex($result, rpv('SELECT u.`mail` FROM @users AS u WHERE u.`id` = # LIMIT 1', $id)))
				{
					$mail = $result[0][0];
				}
				return TRUE;
			}
			else
			{
				$this->error($this->db->get_last_error());
			}
		}

		return FALSE;
	}

	private function load_user_info()
	{
		if(!$this->db->select_ex($result, rpv('SELECT u.`login`, u.`sid`, u.`flags` FROM @users AS u WHERE u.`id` = # AND (u.`flags` & 0x0001) = 0x0000 LIMIT 1', $this->uid)))
		{
			$this->error($this->db->get_last_error());
			return FALSE;
		}

		$this->loaded = TRUE;
		$this->login = $result[0][0];
		$this->token = $result[0][1];
		$this->flags = intval($result[0][2]);

		return TRUE;
	}

	public function get_id()
	{
		return $this->uid;
	}

	public function get_token()
	{
		if(!$this->loaded)
		{
			if(!$this->load_user_info())
			{
				return FALSE;
			}
		}

		return $this->token;
	}

	public function get_login()
	{
		if(!$this->loaded)
		{
			if(!$this->load_user_info())
			{
				return FALSE;
			}
		}

		return $this->login;
	}

	public function is_ldap_user()
	{
		if(!$this->loaded)
		{
			if(!$this->load_user_info())
			{
				return FALSE;
			}
		}

		return $this->flags & UA_LDAP;
	}

	public function is_member($group)
	{
		if($this->uid)
		{
			if(!$this->is_ldap_user())
			{
				return TRUE;  // Internal user is always admin
			}
			else
			{
				/*
				$cookie = '';
				ldap_control_paged_result($this->ldap->get_link(), 200, true, $cookie);

				$sr = ldap_search($this->ldap->get_link(), LDAP_BASE_DN, '(&(objectCategory=person)(objectClass=user)(sAMAccountName='.ldap_escape($this->get_login(), null, LDAP_ESCAPE_FILTER).')(memberOf:1.2.840.113556.1.4.1941:='.ldap_escape($group, null, LDAP_ESCAPE_FILTER).'))', array('samaccountname', 'objectsid'));
				if(!$sr)
				{
					$this->error($this->ldap->get_last_error());
					return FALSE;
				}

				$records = ldap_get_entries($this->ldap->get_link(), $sr);
				if(($records['count'] == 1) && (strcasecmp($records[0]['samaccountname'][0], $this->sam_account_name) == 0))
				{
					return TRUE;
				}
				*/
				if($this->ldap->search($records, '(&(objectCategory=person)(objectClass=user)(sAMAccountName='.ldap_escape($this->get_login(), null, LDAP_ESCAPE_FILTER).')(memberOf:1.2.840.113556.1.4.1941:='.ldap_escape($group, null, LDAP_ESCAPE_FILTER).'))', array('samaccountname', 'objectsid')) != 1)
				{
					return FALSE;
				}

				if(strcasecmp($records[0]['sAMAccountName'][0], $this->get_login()) == 0)
				{
					return TRUE;
				}
			}
		}

		return FALSE;
	}

	private function get_user_rights($object_id)
	{
		$this->rights[$object_id] = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";

		$link = $this->ldap->get_link();

		if(	 $this->uid
			&& $this->is_ldap_user()
			&& $link
			&& $this->db->select_ex($result, rpv("SELECT dn, allow_bits FROM @access WHERE oid = #", $object_id))
		)
		{
			foreach($result as &$row)
			{
				/*
				$cookie = '';
				ldap_control_paged_result($link, 2, true, $cookie);
				//echo '(&(objectClass=user)(sAMAccountName='.ldap_escape($this->sam_account_name, null, LDAP_ESCAPE_FILTER).')(memberOf:1.2.840.113556.1.4.1941:='.$row[0].'))';
				$sr = ldap_search($link, LDAP_BASE_DN, '(&(objectClass=user)(sAMAccountName='.ldap_escape($this->get_login(), null, LDAP_ESCAPE_FILTER).')(memberOf:1.2.840.113556.1.4.1941:='.$row[0].'))', array('samaccountname', 'objectsid'));
				if($sr)
				{
					$records = ldap_get_entries($link, $sr);
					if($records && ($records['count'] == 1))
					{
						for($i = 0; $i <= ((int) (LPD_ACCESS_LAST_BIT / 8)); $i++)
						{
							$this->rights[$object_id][$i] = chr(ord($this->rights[$object_id][$i]) | ord($row[1][$i]));
						}
					}
				}
				ldap_free_result($sr);
				*/
				if($this->ldap->search($records, '(&(objectClass=user)(sAMAccountName='.ldap_escape($this->get_login(), null, LDAP_ESCAPE_FILTER).')(memberOf:1.2.840.113556.1.4.1941:='.$row[0].'))', array('samaccountname', 'objectsid')) == 1)
				{
					for($i = 0; $i <= ((int) (LPD_ACCESS_LAST_BIT / 8)); $i++)
					{
						$this->rights[$object_id][$i] = chr(ord($this->rights[$object_id][$i]) | ord($row[1][$i]));
					}
				}
			}
		}
	}

	/**
	 *  \brief Check user permissions for object
	 *
	 *  \param [in] $object_id Object ID
	 *  \param [in] $level One-based bit number
	 *  \return true - if user have requested permisiions for object. For internal user always true.
	 */

	public function check_permission($object_id, $level)
	{
		if($this->uid && !$this->is_ldap_user())
		{
			return TRUE;  /// Internal user is always admin
		}

		if(!isset($this->rights[$object_id]))
		{
			$this->get_user_rights($object_id);
		}

		$level--;
		return ((ord($this->rights[$object_id][(int) ($level / 8)]) >> ($level % 8)) & 0x01);
	}

	public function get_last_error()
	{
		return $this->error_msg;
	}

	private function error($str)
	{
		if($this->rise_exception)
		{
			throw new Exception(__CLASS__.': '.$str);
		}
		else
		{
			$this->error_msg = $str;
		}
	}
}
