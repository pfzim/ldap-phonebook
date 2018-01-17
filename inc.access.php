<?php
/*
    UserPermissions class - LDAP/AD groups access rights
    Copyright (C) 2018 Dmitry V. Zimin

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

class UserPermissions
{
	private $rights = array();
	private $user_sam = NULL;
	private $db = NULL;
	private $ldap = NULL;

	function __construct($db, $ldap, $user_sam = NULL)
	{
		$this->user_sam = $user_sam;
		$this->db = $db;
		$this->ldap = $ldap;
	}

	public function reset_user($user_sam)
	{
		$this->user_sam = $user_sam;
		$this->rights = array();
	}

	private function get_user_rights($object_id)
	{
		$this->rights[$object_id] = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
		
		$link = $this->ldap->get_link();

		if($link && $this->db->select(rpv("SELECT dn, bits FROM @access WHERE oid = #", $object_id)))
		{
			foreach($db->data as $row)
			{
				$cookie = "";
				ldap_control_paged_result($link, 2, true, $cookie);
				$sr = ldap_search($link, LDAP_BASE_DN, '(&(objectClass=user)(sAMAccountName='.ldap_escape($this->user_sam, null, LDAP_ESCAPE_FILTER).')(memberOf:1.2.840.113556.1.4.1941:='.$row[0].'))', array('samaccountname', 'objectsid'));
				if(!$sr)
				{
					for($i = 0; $i < 10; $i++)
					{
						$this->rights[$object_id][$i] = chr(ord($this->rights[$object_id][$i]) | ord($row[1][$i]));
					}
				}
				ldap_free_result($sr);
			}
		}
	}

	public function check_permission($object_id, $level)
	{
		if(empty($this->user_sam))
		{
			return FALSE
		}

		if(!isset($this->rights[$object_id]))
		{
			$this->get_user_rights($object_id);
		}

		$level--;
		return ((ord($this->rights[$object_id][(int) ($level / 8)]) >> ($level % 8)) & 0x01);
	}
}
