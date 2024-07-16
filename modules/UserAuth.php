<?php
/*
    UserAuth class - Internal or LDAP user authentication and access
	                 control module.
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

/*
	Usage example:

		define('RB_ACCESS_READ', 1);
		define('RB_ACCESS_WRITE', 2);
		define('RB_ACCESS_EXECUTE', 3);
		define('RB_ACCESS_LIST', 4);

		$core->UserAuth->set_bits_representation('rwxl');

	Where 1, 2, 3, 4 are the ordinal numbers of the bits.
*/

define('UA_DISABLED',	0x0001);  /// Ignored for LDAP user
define('UA_LDAP',		0x0002);  /// Is a LDAP user (otherwise is internal user)
define('UA_DELETED',	0x0004);  /// User is deleted
define('UA_ADMIN',		0x0008);  /// User is admin

class UserAuth
{
	private $uid = 0;                    /// User ID

	private $loaded = FALSE;
	private $login = NULL;				/// sAMAccountName, zl cookie
	private $token = NULL;				/// zh cookie
	private $flags = 0;
	private $cookie_path = '/';

	private $salt = 'UserAuth';

	private $ldap = NULL;

	private $bits_string_representation = '';  // like 'rwxd'
	private $max_bits = 0;

	private $rights = array();           // $rights[$object_id] = $bit_flags_permissions

	private $rise_exception = FALSE;

	function __construct(&$core)
	{
		$this->core = &$core;
		$this->rise_exception = FALSE;
		$this->salt = 'UserAuth';

		if(defined('USE_LDAP') && USE_LDAP)
		{
			$this->ldap = &$this->core->LDAP;
		}
		else
		{
			$this->ldap = NULL;
		}

		if(defined('WEB_LINK_BASE_PATH') && !empty(WEB_LINK_BASE_PATH))
		{
			$this->cookie_path = WEB_LINK_BASE_PATH;
		}
		else
		{
			$this->cookie_path = '/';
		}

		$this->bits_string_representation = '';
		$this->max_bits = 0;
		$this->rights = array();
		$this->loaded = FALSE;

		if(empty($_SESSION[DB_PREFIX.'uid']))
		{
			if(!empty($_COOKIE['zh']) && !empty($_COOKIE['zl']))
			{
				$this->logon_by_token($_COOKIE['zl'], $_COOKIE['zh']);
			}
		}
		else
		{
			$this->uid = intval($_SESSION[DB_PREFIX.'uid']);

			/*
			// preload user info

			if($this->core->db->select_ex($user_data, rpv("
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
			", $_SESSION[DB_PREFIX.'uid'])))
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
		$this->logoff();

		if(empty($login) || empty($passwd))
		{
			$this->core->error_ex('Empty login or password!', $this->rise_exception);
			return FALSE;
		}

		if(strpbrk($login, '\\@'))  // LDAP authorization method
		{
			if(!$this->ldap)
			{
				$this->core->error_ex('LDAP class not initialized!', $this->rise_exception);
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
				$this->core->error_ex($this->core->get_last_error(), $this->rise_exception);
				return FALSE;
			}

			if($this->core->db->select_ex($user_data, rpv("
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
						AND (m.`flags` & ({%UA_LDAP})) = {%UA_LDAP}
					LIMIT 1
				", $sam_account_name
			)))
			{
				if(!empty($user_data[0][4]))
				{
					//$this->error('Неверное имя пользователя или пароль!');
					return FALSE;
				}

				$_SESSION[DB_PREFIX.'uid'] = $user_data[0][0];
				$this->login = $user_data[0][1];
				$this->flags = intval($user_data[0][2]);
				$this->token = $user_data[0][3];
			}
			else // add new LDAP user
			{
				$this->token = bin2hex(random_bytes(8));
				$this->login = $sam_account_name;
				$this->flags = UA_LDAP;
				$this->core->db->put(rpv('INSERT INTO @users (login, passwd, mail, sid, flags) VALUES (!, \'\', !, !, #)', $this->login, @$records[0]['mail'][0], $this->token, $this->flags));
				$_SESSION[DB_PREFIX.'uid'] = $this->core->db->last_id();
			}
		}
		else  // internal authorization method
		{
			if(!$this->core->db->select_ex($user_data, rpv("
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
						AND (m.`flags` & ({%UA_DISABLED} | {%UA_LDAP} | {%UA_DELETED})) = 0x0000
					LIMIT 1
				", $login, $passwd.$this->salt
			)))
			{
				//$this->core->db->put(rpv('INSERT INTO `@log` (`date`, `uid`, `type`, `p1`, `ip`) VALUES (NOW(), #, #, #, !)', 0, LOG_LOGIN_FAILED, 0, $ip));
				return FALSE;
			}

			$_SESSION[DB_PREFIX.'uid'] = $user_data[0][0];
			$this->login = $user_data[0][1];
			$this->flags = intval($user_data[0][2]);
			$this->token = $user_data[0][3];
		}

		$this->loaded = TRUE;
		$this->uid = $_SESSION[DB_PREFIX.'uid'];

		if(empty($this->token))
		{
			$this->token = bin2hex(random_bytes(8));
			$this->core->db->put(rpv('UPDATE @users SET `sid` = !, `reset_token` = NULL WHERE `id` = # LIMIT 1', $this->token, $this->uid));
		}

		setcookie('zh', $this->token, time() + 2592000, $this->cookie_path);
		setcookie('zl', $this->login, time() + 2592000, $this->cookie_path);

		//$this->core->db->put(rpv('UPDATE @users SET `sid` = ! WHERE `id` = # LIMIT 1', $this->token, $this->uid));

		//$this->core->db->put(rpv('INSERT INTO `@log` (`date`, `uid`, `type`, `p1`, `ip`) VALUES (NOW(), #, #, #, !)', $this->uid, LOG_LOGIN, 0, $ip));

		return TRUE;
	}

	public function logon_by_token($login, $token)
	{
		if($this->core->db->select_ex($user_data, rpv("
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
				AND (m.`flags` & ({%UA_DISABLED} | {%UA_DELETED})) = 0
			LIMIT 1
		", $login, $token)))
		{
			$this->loaded = TRUE;
			$_SESSION[DB_PREFIX.'uid'] = $user_data[0][0];
			$this->uid = $_SESSION[DB_PREFIX.'uid'];
			$this->flags = intval($user_data[0][1]);
			$this->login = $user_data[0][2];
			$this->token = $user_data[0][3];

			// Extend cookie life time
			setcookie('zh', $this->token, time() + 2592000, $this->cookie_path);
			setcookie('zl', $this->login, time() + 2592000, $this->cookie_path);

			return TRUE;
		}

		return FALSE;
	}

	public function logoff()
	{
		if($this->uid)
		{
			$this->core->db->put(rpv('UPDATE @users SET `sid` = NULL, `reset_token` = NULL WHERE `id` = # LIMIT 1', $this->uid));
		}

		$_SESSION[DB_PREFIX.'uid'] = 0;
		setcookie('zh', '', time() - 60, $this->cookie_path);
		setcookie('zl', '', time() - 60, $this->cookie_path);

		$this->loaded = FALSE;
		$this->uid = 0;
		$this->flags = 0;
		$this->login = NULL;
		$this->token = NULL;
	}

	public function add($login, $passwd, $mail)
	{
		if($this->core->db->select(rpv('SELECT u.`id` FROM @users AS u WHERE (u.`login`= ! OR u.`mail` = !) AND (`flags` & ({%UA_DELETED} | {%UA_LDAP})) = 0 LIMIT 1', $login, $mail)))
		{
			$this->core->error_ex('User already exist!', $this->rise_exception);
			return 0;
		}

		if(!$this->core->db->put(rpv('INSERT INTO @users (login, passwd, mail, flags) VALUES (!, MD5(!), !, {%UA_DISABLED})', $login, $passwd.$this->salt, $mail)))
		{
			$this->core->error_ex($this->core->get_last_error(), $this->rise_exception);
			return 0;
		}

		return $this->core->db->last_id();
	}

	public function reset_password($uid, $token, $passwd)
	{
		$affected = 0;

		if($this->core->db->select(rpv('SELECT u.`id` FROM @users AS u WHERE u.`id` = # AND u.`reset_token` = ! AND (u.`flags` & ({%UA_DISABLED} | {%UA_LDAP} | {%UA_DELETED})) = 0 LIMIT 1', $uid, $token)))
		{
			if($this->core->db->put(rpv('UPDATE `@users` SET `passwd` = MD5(!), `reset_token` = NULL WHERE `id` = # AND `reset_token` = ! LIMIT 1', $passwd.$this->salt, $uid, $token), $affected))
			{
				//return ($affected > 0);
				return TRUE;
			}
		}

		return FALSE;
	}

	public function make_reset_token($uid, &$token)
	{
		$token = bin2hex(random_bytes(8));

		if($this->core->db->put(rpv('UPDATE `@users` SET `reset_token` = ! WHERE `id` = # AND (`flags` & ({%UA_DISABLED} | {%UA_LDAP} | {%UA_DELETED})) = 0 LIMIT 1', $token, $uid)))
		{
			return TRUE;
		}

		return FALSE;
	}

	public function change_password_ex($uid, $passwd)
	{
		$affected = 0;

		if($uid)
		{
			if($this->core->db->put(rpv('UPDATE `@users` SET `passwd` = MD5(!) WHERE `id` = # AND (`flags` & ({%UA_DISABLED} | {%UA_LDAP} | {%UA_DELETED})) = 0 LIMIT 1', $passwd.$this->salt, $uid), $affected))
			{
				//return ($affected > 0);
				return TRUE;
			}
		}

		return FALSE;
	}

	public function change_password($passwd)
	{
		if($this->uid && !$this->is_ldap_user())  // Only internal user can change self password
		{
			return $this->change_password_ex($this->uid, $passwd);
		}

		return FALSE;
	}

	public function check_password($passwd)
	{
		if($this->uid && !$this->is_ldap_user())  // Only internal user can change self password
		{
			if($this->core->db->select_ex($user, rpv('SELECT u.`id` FROM `@users` AS u WHERE u.`id` = # AND u.`passwd` = MD5(!) AND (u.`flags` & ({%UA_DISABLED} | {%UA_LDAP} | {%UA_DELETED})) = 0 LIMIT 1', $this->uid, $passwd.$this->salt)))
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
	 *  \brief Update user info
	 *
	 *  \param [in] $uid User ID. If 0 then user will be created
	 *  \param [in] $login User login
	 *  \param [in] $mail User mail address for send notification
	 *  \param [in] $is_admin User is local administrator
	 *  \return true - if activated successfully
	 */

	public function set_user_info_ex($uid, $login, $mail, $is_admin = FALSE)
	{
		$affected = 0;

		if($uid)
		{
			if($this->core->db->put(rpv('UPDATE `@users` SET `login` = !, `mail` = !, `flags` = ((`flags` & ~{%UA_ADMIN}) | #) WHERE `id` = # AND (`flags` & ({%UA_LDAP})) = 0 LIMIT 1', $login, $mail, $is_admin ? UA_ADMIN : 0, $uid), $affected))
			{
				//return ($affected > 0);
				return TRUE;
			}
		}
		else
		{
			if(!$this->core->db->select_ex($users, rpv('SELECT u.`id` FROM `@users` AS u WHERE (u.`login` = ! OR u.`mail` = !) AND (u.`flags` & ({%UA_DELETED} | {%UA_LDAP})) = 0 LIMIT 1', $login, $mail)))
			{
				if($this->core->db->put(rpv('INSERT INTO `@users` (`login`, `mail`, `passwd`, `flags`) VALUES (!, !, \'\', #)', $login, $mail, $is_admin ? UA_ADMIN : 0)))
				{
					return TRUE;
				}
			}
			else
			{
				$this->core->error_ex('User or mail already exist!', $this->rise_exception);
			}
		}

		return FALSE;
	}

	public function delete_user_ex($uid)
	{
		$affected = 0;

		if($uid)
		{
			if($this->core->db->put(rpv('UPDATE `@users` SET `flags` = (`flags` | {%UA_DELETED}) WHERE `id` = # AND (`flags` & ({%UA_LDAP} | {%UA_DELETED})) = 0 LIMIT 1', $uid), $affected))
			{
				//return ($affected > 0);
				return TRUE;
			}
		}

		return FALSE;
	}

	public function deactivate_user_ex($uid)
	{
		$affected = 0;

		if($uid)
		{
			if($this->core->db->put(rpv('UPDATE `@users` SET `flags` = (`flags` | {%UA_DISABLED}) WHERE `id` = # AND (`flags` & {%UA_LDAP}) = 0 LIMIT 1', $uid), $affected))
			{
				//return ($affected > 0);
				return TRUE;
			}
		}

		return FALSE;
	}

	public function activate_user_ex($uid)
	{
		$affected = 0;

		if($uid)
		{
			if($this->core->db->put(rpv('UPDATE `@users` SET `flags` = (`flags` & ~{%UA_DISABLED}) WHERE `id` = # AND (`flags` & {%UA_LDAP}) = 0 LIMIT 1', $uid), $affected))
			{
				//return ($affected > 0);
				return TRUE;
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
		if($this->uid && !$this->is_ldap_user() && (($this->flags & UA_ADMIN) == UA_ADMIN))  // Only internal admin user can activate
		{
			if($this->core->db->put(rpv('UPDATE @users SET `flags` = (`flags` & ~{%UA_DISABLED}) WHERE `login` = ! AND `id` = #', $login, $id)))
			{
				//$this->core->db->put(rpv('INSERT INTO `@log` (`date`, `uid`, `type`, `p1`, `ip`) VALUES (NOW(), #, #, #, !)', 0, LOG_LOGIN_ACTIVATE, $id, $ip));
				$mail = FALSE;
				if($this->core->db->select_ex($result, rpv('SELECT u.`mail` FROM @users AS u WHERE u.`id` = # LIMIT 1', $id)))
				{
					$mail = $result[0][0];
				}
				return TRUE;
			}
			else
			{
				$this->core->error_ex($this->core->get_last_error(), $this->rise_exception);
			}
		}

		return FALSE;
	}

	/**
	 *  \brief Find user by mail address. This function required for reset password
	 *
	 *  \param [in/out] $mail User mail address. This value replaced by value from database
	 *  \return user_id or 0
	 */

	public function find_user_by_mail(&$mail)
	{
		if($this->core->db->select_ex($result, rpv('SELECT u.`id`, u.`mail` FROM @users AS u WHERE u.`mail` = ! AND (u.`flags` & ({%UA_DISABLED} | {%UA_LDAP})) = 0 LIMIT 1', $mail)))
		{
			$mail = $result[0][1];
			return intval($result[0][0]);
		}

		return 0;
	}

	private function load_user_info()
	{
		if(!$this->core->db->select_ex($result, rpv('SELECT u.`login`, u.`sid`, u.`flags` FROM @users AS u WHERE u.`id` = # AND (u.`flags` & ({%UA_DISABLED} | {%UA_DELETED})) = 0 LIMIT 1', $this->uid)))
		{
			$this->core->error_ex($this->core->get_last_error(), $this->rise_exception);
			return FALSE;
		}

		$this->loaded = TRUE;
		$this->login = $result[0][0];
		$this->token = $result[0][1];
		$this->flags = intval($result[0][2]);

		return TRUE;
	}

	public function get_user_info_ex($uid)
	{
		if(!$this->core->db->select_ex($result, rpv('SELECT u.`id`, u.`login`, u.`sid`, u.`mail`, u.`flags` FROM @users AS u WHERE u.`id` = # LIMIT 1', $uid)))
		{
			return FALSE;
		}

		return array(
			'id' => $result[0][0],
			'login' => $result[0][1],
			'sid' => $result[0][2],
			'mail' => $result[0][3],
			'flags' => intval($result[0][4])
		);
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
				return (($this->flags & UA_ADMIN) == UA_ADMIN);  // Return TRUE if internal user is admin
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

	/**
	 *  \brief Get user permissions for object directly from DB and LDAP
	 *
	 *  \param [in] $object_id Object ID
	 *  \return void
	 */

	private function get_user_rights($object_id)
	{
		$this->rights[$object_id] = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";

		$link = $this->ldap->get_link();

		if(	$this->uid
			&& $this->is_ldap_user()
			&& $link
			&& $this->core->db->select_ex($result, rpv("SELECT `sid`, `dn`, `allow_bits` FROM @access WHERE `oid` = #", $object_id))
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
				
				if(defined('LDAP_USE_SID') && LDAP_USE_SID)
				{
					if($this->ldap->search($records, '(&(objectCategory=group)(objectSID='.$row[0].'))', array('distinguishedName')) != 1)
					{
						continue;
					}

					$group_dn = $records[0]['distinguishedName'][0];
				}
				else
				{
					$group_dn = $row[1];
				}
				
				if($this->ldap->search($records, '(&(objectClass=user)(objectCategory=person)(sAMAccountName='.ldap_escape($this->get_login(), null, LDAP_ESCAPE_FILTER).')(memberOf:1.2.840.113556.1.4.1941:='.$group_dn.'))', array('samaccountname', 'objectsid')) == 1)
				{
					$this->rights[$object_id] = $this->merge_permissions($this->rights[$object_id], $row[2]);

					//log_file('Object: '.$object_id.', Group: '.$row[0].', Perm: '.$this->permissions_to_string($this->rights[$object_id]));
					/*
					for($i = 0; $i <= ((int) ($this->max_bits / 8)); $i++)
					{
						$this->rights[$object_id][$i] = chr(ord($this->rights[$object_id][$i]) | ord($row[1][$i]));
					}
					*/
				}
			}
		}

		if(defined('USE_MEMCACHED') && USE_MEMCACHED)
		{
			$this->core->Mem->set($this->get_id().'_'.$object_id, $this->rights[$object_id]);
		}
	}

	public function merge_permissions($rights, $added_rights)
	{
		for($i = 0; $i <= ((int) ($this->max_bits / 8)); $i++)
		{
			$rights[$i] = chr(ord($rights[$i]) | ord($added_rights[$i]));
		}

		return $rights;
	}

	/**
	 *  \brief Check user permissions for object
	 *
	 *  \param [in] $object_id Object ID
	 *  \param [in] $level One-based ordinal number of bit
	 *  \return true - if user have requested permissions for object. For internal user always true.
	 */

	public function check_permission($object_id, $level)
	{
		$object_id = intval($object_id);

		if(!$this->uid)
		{
			return FALSE;  /// Not logged in user always return FALSE
		}

		if(!$this->is_ldap_user())
		{
			return (($this->flags & UA_ADMIN) == UA_ADMIN);  /// Return TRUE if internal user is admin
		}

		if(!isset($this->rights[$object_id]))  // local cache empty
		{
			if(!defined('USE_MEMCACHED') || !USE_MEMCACHED || !$this->core->Mem->get($this->get_id().'_'.$object_id, $this->rights[$object_id]))  // memcached empty
			{
				$this->get_user_rights($object_id); // get from DB if local cache and memcached fail
			}
		}

		$level--;

		//log_file('Object: '.$object_id.', Level: '.$level.', Perm: '.$this->permissions_to_string($this->rights[$object_id]).', Result: '.((ord($this->rights[$object_id][(int) ($level / 8)]) >> ($level % 8)) & 0x01).': '.(($level % 8)));
		return ((ord($this->rights[$object_id][(int) ($level / 8)]) >> ($level % 8)) & 0x01);
	}

	public function set_bits_representation($representation)
	{
		$this->bits_string_representation = $representation;
		$this->max_bits = strlen($representation);
	}

	public function permissions_to_string($allow_bits)
	{
		$result = '';
		$bits_count = strlen($this->bits_string_representation);

		for($i = 0; $i < $bits_count; $i++)
		{
			if((ord($allow_bits[(int) ($i / 8)]) >> ($i % 8)) & 0x01)
			{
				$result .= $this->bits_string_representation[$i];
			}
			else
			{
				$result .= '-';
			}
		}
		return $result;
	}

	public function set_rise_exception($rise_exception)
	{
		$this->rise_exception = $rise_exception;
	}
}

/**
 *  \brief Set and Unset bits
 *
 *  \param [in/out] $bits Existings bits
 *  \param [in] $bit One-based ordinal number of bit
 *  \return Nothing
 */

function set_permission_bit(&$bits, $bit)
{
	$bit--;
	$bits[(int) ($bit / 8)] = chr(ord($bits[(int) ($bit / 8)]) | (0x1 << ($bit % 8)));
}

function unset_permission_bit(&$bits, $bit)
{
	$bit--;
	$bits[(int) ($bit / 8)] = chr(ord($bits[(int) ($bit / 8)]) & ((0x1 << ($bit % 8)) ^ 0xF));
}


// https://stackoverflow.com/questions/39533560/php-ldap-get-user-sid
function bin_to_str_sid($binary_sid) {

    $sid = NULL;
    /* 64bt PHP */
    if(strlen(decbin(~0)) == 64)
    {
        // Get revision, indentifier, authority 
        $parts = unpack('Crev/x/nidhigh/Nidlow', $binary_sid);
        // Set revision, indentifier, authority 
        $sid = sprintf('S-%u-%d',  $parts['rev'], ($parts['idhigh']<<32) + $parts['idlow']);
        // Translate domain
        $parts = unpack('x8/V*', $binary_sid);
        // Append if parts exists
        if ($parts) $sid .= '-';
        // Join all
        $sid.= join('-', $parts);
    }
    /* 32bit PHP */
    else
    {   
        $sid = 'S-';
        $sidinhex = str_split(bin2hex($binary_sid), 2);
        // Byte 0 = Revision Level
        $sid = $sid.hexdec($sidinhex[0]).'-';
        // Byte 1-7 = 48 Bit Authority
        $sid = $sid.hexdec($sidinhex[6].$sidinhex[5].$sidinhex[4].$sidinhex[3].$sidinhex[2].$sidinhex[1]);
        // Byte 8 count of sub authorities - Get number of sub-authorities
        $subauths = hexdec($sidinhex[7]);
        //Loop through Sub Authorities
        for($i = 0; $i < $subauths; $i++) {
            $start = 8 + (4 * $i);
            // X amount of 32Bit (4 Byte) Sub Authorities
            $sid = $sid.'-'.hexdec($sidinhex[$start+3].$sidinhex[$start+2].$sidinhex[$start+1].$sidinhex[$start]);
        }
    }
    return $sid;
}
