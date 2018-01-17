<?php
/*
    LDAP class - connect LDAP on demand
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

class LDAP
{
	private $link = NULL;
	private $error_msg = "";
	private $ldap_host = NULL;
	private $ldap_port = NULL;
	private $ldap_user = NULL;
	private $ldap_passwd = NULL;
	private $rise_exception = FALSE;

	function __construct($ldap_host, $ldap_port, $ldap_user, $ldap_passwd, $rise_exception = FALSE)
	{
		$this->ldap_host = $ldap_host;
		$this->ldap_port = $ldap_port;
		$this->ldap_user = $ldap_user;
		$this->ldap_passwd = $ldap_passwd;
		$this->link = NULL;
		$this->error_msg = "";
		$this->rise_exception = $rise_exception;
	}

	private function connect()
	{
		$this->link = @ldap_connect($this->ldap_host, $this->ldap_port);
		if(!$this->link)
		{
			$this->error(ldap_error($this->link));
			$this->link = NULL;
			return FALSE;
		}

		ldap_set_option($this->link, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($this->link, LDAP_OPT_REFERRALS, 0);
		
		if(!@ldap_bind($this->link, $this->ldap_user, $this->ldap_passwd))
		{
			$this->error(ldap_error($this->link));
			ldap_unbind($this->link);
			$this->link = NULL;
			return FALSE;
		}
		
		return TRUE;
	}

	public function disconnect()
	{
		$this->error_msg = "";

		if($this->link)
		{
			ldap_unbind($this->link);
			$this->link = NULL;
		}
	}

	public function __destruct()
	{
		$this->data = array();
		$this->disconnect();
	}

	// needed for check user password
	public function switch_user($ldap_user, $ldap_passwd, $force_connect = FALSE)
	{
		$this->ldap_user = $ldap_user;
		$this->ldap_passwd = $ldap_passwd;
		
		if($this->link)
		{
			if(!@ldap_bind($this->link, $this->ldap_user, $this->ldap_passwd))
			{
				$this->error(ldap_error($this->link));
				$this->link = NULL;
				return FALSE;
			}
		}
		else if($force_connect)
		{
			return $this->connect();
		}
		
		return TRUE;
	}
	
	public function get_link($db_name)
	{
		if(!$this->link)
		{
			$this->connect();
		}
		
		return $this->link;
	}

	public function get_last_error()
	{
		return $this->error_msg;
	}

	private function error($str)
	{
		if($this->rise_exception)
		{
			throw new Exception(__CLASS__.": ".$str);
		}
		else
		{
			$this->error_msg = $str;
		}
	}
}
