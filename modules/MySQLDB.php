<?php
/*
    MySQLDB class - connect on demand and allow read from one server
                    and write to another server
    Copyright (C) 2017-2020 Dmitry V. Zimin

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

/**
	Class for operate with MySQL database
*/

class MySQLDB
{
	private $core = NULL;
	private $link_ro = NULL;
	private $link_rw = NULL;
	private $db_ro_host = NULL;
	private $db_rw_host = NULL;
	private $db_user = NULL;
	private $db_passwd = NULL;
	private $db_cpage = NULL;
	private $db_name = NULL;
	private $db_ro_selected = FALSE;
	private $db_rw_selected = FALSE;
	private $db_ro_same_rw = FALSE;
	private $transaction_started = 0;
	public $data = array();

	/**
	 Constructor.
	 Constructor does not actualy connect to DB.
		\param [in] $db_rw_host Master DB host for write data
		\param [in] $db_ro_host Slave DB host for read data
		\param [in] $db_user User name
		\param [in] $db_passwd Password
		\param [in] $db_name Database name
		\param [in] $db_cpage Codepage
		\param [in] $rise_exception [optional] default value FALSE
	*/

	function __construct(&$core)
	{
		$this->core = &$core;
		
		/*
		$this->db_ro_host = $this->core->get_config('DB_RO_HOST');
		$this->db_rw_host = $this->core->get_config('DB_RW_HOST');
		$this->db_user = $this->core->get_config('DB_USER');
		$this->db_passwd = $this->core->get_config('DB_PASSWD');
		$this->db_cpage = $this->core->get_config('DB_CPAGE');
		$this->db_name = $this->core->get_config('DB_NAME');
		*/
		
		$this->db_ro_host = '';
		$this->db_rw_host = DB_RW_HOST;
		$this->db_user = DB_USER;
		$this->db_passwd = DB_PASSWD;
		$this->db_cpage = DB_CPAGE;
		$this->db_name = DB_NAME;
		
		$this->db_ro_selected = FALSE;
		$this->db_rw_selected = FALSE;
		$this->link_ro = NULL;
		$this->link_rw = NULL;
		$this->db_ro_same_rw = empty($db_ro_host);
		$this->transaction_started = 0;
		$this->data = array();
	}

	/**
	 Connect to DB host.
	 If transaction started, then connecting to RW host.
		\param [in] $read_only bool true - if need connect to Read-only DB
	*/

	private function connect($read_only)
	{
		if(!$read_only || $this->db_ro_same_rw || $this->transaction_started)
		{
			if(!$this->link_rw)
			{
				$this->link_rw = mysqli_connect($this->db_rw_host, $this->db_user, $this->db_passwd, $this->db_name);
				if(!$this->link_rw)
				{
					$this->core->error(mysqli_connect_error());
					return FALSE;
				}
				$this->db_rw_selected = TRUE;

				if(!mysqli_set_charset($this->link_rw, $this->db_cpage))
				{
					$this->core->error(mysqli_error($this->link_rw));
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
					$this->core->error(mysqli_connect_error());
					return FALSE;
				}

				$this->db_ro_selected = TRUE;

				if(!mysqli_set_charset($this->link_ro, $this->db_cpage))
				{
					$this->core->error(mysqli_error($this->link_ro));
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

	/**
		Disconnect from DB.
	*/

	public function disconnect()
	{
		//$this->data = FALSE;
		//$this->error_msg = '';

		if($this->link_ro)
		{
			mysqli_close($this->link_ro);
			$this->link_ro = NULL;
		}

		if($this->link_rw)
		{
			if($this->transaction_started)
			{
				$this->rollback();
			}
			mysqli_close($this->link_rw);
			$this->link_rw = NULL;
		}
	}

	public function __destruct()
	{
		$this->data = array();
		$this->disconnect();
	}

	public function start_transaction()
	{
		$this->put('START TRANSACTION');
		$this->transaction_started = 1;
	}
	
	public function commit()
	{
		$this->put('COMMIT');
		$this->transaction_started = 0;
	}

	public function rollback()
	{
		$this->put('ROLLBACK');
		$this->transaction_started = 0;
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
		return $this->select_ex($this->data, $query);
	}

	public function select_ex(&$data, $query)
	{
		$data = array();

		if(!$this->connect(TRUE))
		{
			return FALSE;
		}

		$res = mysqli_query(($this->db_ro_same_rw || $this->transaction_started) ? $this->link_rw : $this->link_ro, $query);
		if(!$res)
		{
			$this->core->error(mysqli_error(($this->db_ro_same_rw || $this->transaction_started) ? $this->link_rw : $this->link_ro));
			return FALSE;
		}

		if(mysqli_num_rows($res) <= 0)
		{
			return FALSE;
		}

		while($row = mysqli_fetch_row($res))
		{
			$data[] = $row;
		}

		mysqli_free_result($res);

		return TRUE;
	}

	public function select_assoc($query)
	{
		return $this->select_assoc_ex($this->data, $query);
	}

	public function select_assoc_ex(&$data, $query)
	{
		$data = array();

		if(!$this->connect(TRUE))
		{
			return FALSE;
		}

		$res = mysqli_query(($this->db_ro_same_rw || $this->transaction_started) ? $this->link_rw : $this->link_ro, $query);
		if(!$res)
		{
			$this->core->error(mysqli_error(($this->db_ro_same_rw || $this->transaction_started) ? $this->link_rw : $this->link_ro));
			return FALSE;
		}

		if(mysqli_num_rows($res) <= 0)
		{
			return FALSE;
		}

		while($row = mysqli_fetch_assoc($res))
		{
			$data[] = $row;
		}

		mysqli_free_result($res);

		return TRUE;
	}

	public function put($query, &$affected_rows = NULL)
	{
		if(!$this->connect(FALSE))
		{
			return FALSE;
		}

		$res = mysqli_query($this->link_rw, $query);
		if(!$res)
		{
			$this->core->error(mysqli_error($this->link_rw));
			return FALSE;
		}

		if(func_num_args() > 1)
		{
			$affected_rows = mysqli_affected_rows($this->link_rw);
		}

		return TRUE;
	}

	public function last_id()
	{
		return mysqli_insert_id($this->link_rw);
	}
}