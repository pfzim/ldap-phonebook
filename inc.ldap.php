<?php
// connect LDAP on demand
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
		$this->link = ldap_connect(LDAP_HOST, LDAP_PORT);
		if(!$this->link)
		{
			$this->error(ldap_error($this->link));
			$this->link = NULL;
		}

		ldap_set_option($this->link, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($this->link, LDAP_OPT_REFERRALS, 0);
		
		if(!ldap_bind($this->link, LDAP_USER, LDAP_PASSWD))
		{
			$this->error(ldap_error($this->link));
			$this->link = NULL;
		}
	}

	public function disconnect()
	{
		$this->error_msg = "";

		if($this->link)
		{
			ldap_unbind($ldap);
			$this->link = NULL;
		}
	}

	public function __destruct()
	{
		$this->data = array();
		$this->disconnect();
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
