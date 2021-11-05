<?php
/*
    LDAP class - connect LDAP on demand
    Copyright (C) 2018-2020 Dmitry V. Zimin

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
	private $ldap_uri = NULL;
	private $ldap_user = NULL;
	private $ldap_passwd = NULL;
	private $core = NULL;
	private $rise_exception = FALSE;
	private $use_gssapi = FALSE;

	function __construct(&$core)
	{
		$this->core = &$core;

		$this->ldap_uri = LDAP_URI;
		$this->ldap_user = LDAP_USER;
		$this->ldap_passwd = LDAP_PASSWD;
		$this->link = NULL;
		$this->rise_exception = FALSE;
		$this->use_gssapi = (defined('USE_GSSAPI') && USE_GSSAPI);
	}

	private function connect()
	{
		$this->link = @ldap_connect($this->ldap_uri);
		if(!$this->link)
		{
			$this->core->error_ex(ldap_error($this->link), $this->rise_exception);
			$this->link = NULL;
			return FALSE;
		}

		ldap_set_option($this->link, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($this->link, LDAP_OPT_REFERRALS, 0);

		if($this->use_gssapi)
		{
			$result = @ldap_sasl_bind($this->link, NULL, NULL, 'GSSAPI');
		}
		else
		{
			$result = @ldap_bind($this->link, $this->ldap_user, $this->ldap_passwd);
		}

		if(!$result)
		{
			$this->core->error_ex(ldap_error($this->link), $this->rise_exception);
			ldap_unbind($this->link);
			$this->link = NULL;
			return FALSE;
		}

		return TRUE;
	}

	public function disconnect()
	{
		//$this->error_msg = '';

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
	public function reset_user($ldap_user, $ldap_passwd, $force_connect = FALSE)
	{
		$this->use_gssapi = FALSE;
		$this->ldap_user = $ldap_user;
		$this->ldap_passwd = $ldap_passwd;

		if($this->link)
		{
			if(!@ldap_bind($this->link, $this->ldap_user, $this->ldap_passwd))
			{
				$this->core->error_ex(ldap_error($this->link), $this->rise_exception);
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

	public function get_link()
	{
		if(!$this->link)
		{
			$this->connect();
		}

		return $this->link;
	}

	public function search(&$result, $query, $attrs)
	{
		$result = array();
		$lnk = $this->get_link();

		$search_result = ldap_search($lnk, LDAP_BASE_DN, $query, $attrs);
		if(!$search_result)
		{
			$this->core->error_ex(ldap_error($lnk), $this->rise_exception);
			return FALSE;
		}

		$i = 0;
		$record = ldap_first_entry($lnk, $search_result);
		while($record)
		{
			$result[] = ldap_get_attributes($lnk, $record);
			$i++;
			$record = ldap_next_entry($lnk, $record);
		}

		ldap_free_result($search_result);

		//log_file('LDAP: '.$i.' : '.$query);

		return $i;
	}

	public function set_rise_exception($rise_exception)
	{
		$this->rise_exception = $rise_exception;
	}
}
