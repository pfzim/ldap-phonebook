<?php

class UserPermissions
{
	private $rights = array();
	private $user_sam = NULL;
	private $db = NULL;
	private $ldap = NULL;
	
	function __construct($user_sam, $db, $ldap)
	{
		$this->user_sam = $user_sam;
		$this->db = $db;
		$this->ldap = $ldap;
	}
	
	private function get_user_rights($object_id)
	{
		$this->rights[$object_id] = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";

		if($this->db->select(rpv("SELECT dn, bits FROM z_access WHERE oid = #", $object_id)))
		{
			$link = $this->ldap->get_link();
			foreach($db->data as $row)
			{
				$cookie = "";
				ldap_control_paged_result($link, 200, true, $cookie);
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
		if(!isset($this->rights[$object_id]))
		{
			$this->get_user_rights($object_id);
		}

		if(isset($this->rights[$object_id]))
		{
			return ((ord($this->rights[$object_id][(int) (($level-1) / 8)]) >> (($level-1) % 8)) & 0x01);
		}
		
		return FALSE;
	}
}
