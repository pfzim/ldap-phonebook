<?php
/*
    PostgreSQLDB class - connect on demand
    Copyright (C) 2017-2023 Dmitry V. Zimin

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
	Class for operate with PostgreSQL database
*/

class PostgreSQLDB
{
	private $core = NULL;
	private $link_rw = NULL;
	private $db_rw_host = NULL;
	private $db_user = NULL;
	private $db_passwd = NULL;
	private $db_name = NULL;
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
		
		$this->db_rw_host = PG_RW_HOST;
		$this->db_user = PG_USER;
		$this->db_passwd = PG_PASSWD;
		$this->db_name = PG_NAME;
		
		$this->link_rw = NULL;
		$this->transaction_started = 0;
		$this->data = array();
	}

	/**
	 Connect to DB host.
	 If transaction started, then connecting to RW host.
		\param [in] $read_only bool true - if need connect to Read-only DB
	*/

	private function connect()
	{
		if(!$this->link_rw)
		{
			$this->link_rw = pg_connect('host='.$this->db_rw_host.' dbname='.$this->db_name.' user='.$this->db_user.' password='.$this->db_passwd);
			if(!$this->link_rw)
			{
				$this->core->error(pg_last_error());
				return FALSE;
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

		if($this->link_rw)
		{
			if($this->transaction_started)
			{
				$this->rollback();
			}
			pg_close($this->link_rw);
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
		return FALSE;
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

		$res = pg_query($this->link_rw, $query);
		if(!$res)
		{
			$this->core->error(pg_last_error($this->link_rw));
			return FALSE;
		}

		if(pg_num_rows($res) <= 0)
		{
			return FALSE;
		}

		while($row = pg_fetch_row($res))
		{
			$data[] = $row;
		}

		pg_free_result($res);

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

		$res = pg_query($this->link_rw, $query);
		if(!$res)
		{
			$this->core->error(pg_last_error($this->link_rw));
			return FALSE;
		}

		if(pg_num_rows($res) <= 0)
		{
			return FALSE;
		}

		while($row = pg_fetch_assoc($res))
		{
			$data[] = $row;
		}

		pg_free_result($res);

		return TRUE;
	}

	public function put($query, &$affected_rows = NULL)
	{
		if(!$this->connect(FALSE))
		{
			return FALSE;
		}

		$res = pg_query($this->link_rw, $query);
		if(!$res)
		{
			$this->core->error(pg_last_error($this->link_rw));
			return FALSE;
		}

		if(func_num_args() > 1)
		{
			$affected_rows = pg_affected_rows($this->link_rw);
		}

		return TRUE;
	}

	public function last_id()
	{
		if($this->select_ex($result, 'SELECT lastval()'))
		{
			return $result[0][0];
		}
		
		return FALSE;
	}
}
