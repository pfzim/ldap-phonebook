<?php
class MySQLDB
{
	private $link_ro = NULL;
	private $link_rw = NULL;
	private $error_msg = "";
	private $db_ro_host = NULL;
	private $db_rw_host = NULL;
	private $db_user = NULL;
	private $db_passwd = NULL;
	private $db_cpage = NULL;
	private $db_name = NULL;
	private $db_ro_selected = FALSE;
	private $db_rw_selected = FALSE;
	private $db_ro_same_rw = FALSE;
	private $rise_exception = FALSE;
	public $data = array();

	function __construct($db_rw_host, $db_ro_host, $db_user, $db_passwd, $db_name, $db_cpage, $rise_exception = FALSE)
	{
		$this->db_ro_host = $db_ro_host;
		$this->db_rw_host = $db_rw_host;
		$this->db_user = $db_user;
		$this->db_passwd = $db_passwd;
		$this->db_cpage = $db_cpage;
		$this->db_name = $db_name;
		$this->db_ro_selected = FALSE;
		$this->db_rw_selected = FALSE;
		$this->link_ro = NULL;
		$this->link_rw = NULL;
		$this->db_ro_same_rw = empty($db_ro_host);
		$this->data = array();
		$this->error_msg = "";
		$this->rise_exception = $rise_exception;
	}

	private function connect($read_only)
	{
		if(!$read_only || $this->db_ro_same_rw)
		{
			if(!$this->link_rw)
			{
				$this->link_rw = mysqli_connect($this->db_rw_host, $this->db_user, $this->db_passwd, $this->db_name);
				if(!$this->link_rw)
				{
					$this->error(mysqli_connect_error());
					return FALSE;
				}
				$this->db_rw_selected = TRUE;

				if(!mysqli_set_charset($this->link_rw, $this->db_cpage))
				{
					$this->error(mysqli_error($this->link_rw));
					mysqli_close($this->link_rw);
					$this->link_rw = NULL;
					return FALSE;
				}
			}

			if(!$this->db_rw_selected)
			{
				if(!mysqli_select_db($this->link_rw, $this->db_name))
				{
					return FALSE;
				}
				$this->db_rw_selected = TRUE;
			}
		}
		else
		{
			if(!$this->link_ro)
			{
				$this->link_ro = mysqli_connect($this->db_ro_host, $this->db_user, $this->db_passwd, $this->db_name);
				if(!$this->link_ro)
				{
					$this->error(mysqli_connect_error());
					return FALSE;
				}

				$this->db_ro_selected = TRUE;
				
				if(!mysqli_set_charset($this->link_ro, $this->db_cpage))
				{
					$this->error(mysqli_error($this->link_ro));
					mysqli_close($this->link_ro);
					$this->link_ro = NULL;
					return FALSE;
				}
			}

			if(!$this->db_ro_selected)
			{
				if(!mysqli_select_db($this->link_ro, $this->db_name))
				{
					return FALSE;
				}
				$this->db_ro_selected = TRUE;
			}
		}

		return TRUE;
	}

	public function disconnect()
	{
		//$this->data = FALSE;
		$this->error_msg = "";

		if($this->link_ro)
		{
			mysqli_close($this->link_ro);
			$this->link_ro = NULL;
		}

		if($this->link_rw)
		{
			mysqli_close($this->link_rw);
			$this->link_rw = NULL;
		}
	}

	public function __destruct()
	{
		$this->data = array();
		$this->disconnect();
	}

	public function select_db($db_name)
	{
		$this->db_name = $db_name;
		$this->db_ro_selected = FALSE;
		$this->db_rw_selected = FALSE;
		//return mysqli_select_db($this->link_ro, $db_name);
	}

	public function select($query)
	{
		$this->data = array();

		if(!$this->connect(TRUE))
		{
			return FALSE;
		}

		$res = mysqli_query($this->db_ro_same_rw ? $this->link_rw : $this->link_ro, $query);
		if(!$res)
		{
			$this->error(mysqli_error($this->db_ro_same_rw ? $this->link_rw : $this->link_ro));
			return FALSE;
		}

		if(mysqli_num_rows($res) <= 0)
		{
			return FALSE;
		}

		while($row = mysqli_fetch_row($res))
		{
			$this->data[] = $row;
		}

		mysqli_free_result($res);

		return TRUE;
	}

	public function select_assoc($query)
	{
		$this->data = array();

		if(!$this->connect(TRUE))
		{
			return FALSE;
		}

		$res = mysqli_query($this->db_ro_same_rw ? $this->link_rw : $this->link_ro, $query);
		if(!$res)
		{
			$this->error(mysqli_error($this->db_ro_same_rw ? $this->link_rw : $this->link_ro));
			return FALSE;
		}

		if(mysqli_num_rows($res) <= 0)
		{
			return FALSE;
		}

		while($row = mysqli_fetch_assoc($res))
		{
			$this->data[] = $row;
		}

		mysqli_free_result($res);

		return TRUE;
	}

	public function put($query)
	{
		if(!$this->connect(FALSE))
		{
			return FALSE;
		}

		$res = mysqli_query($this->link_rw, $query);
		if(!$res)
		{
			$this->error(mysqli_error($this->link_rw));
			return FALSE;
		}

		//return mysqli_affected_rows($this->link_rw);
		return TRUE;
	}

	public function last_id()
	{
		return mysqli_insert_id($this->link_rw);
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
