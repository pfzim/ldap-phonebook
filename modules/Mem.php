<?php
/*
    Mem class - memcached.
    Copyright (C) 2021 Dmitry V. Zimin

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

class Mem
{
	private $core = NULL;
	private $mc = NULL;

	function __construct(&$core)
	{
		$this->core = &$core;
		$this->mc = NULL;
	}

	private function connect()
	{
		if(!$this->mc)
		{
			$this->mc = new Memcached();
			$this->mc->addServer('localhost', 11211);
		}
	}

	public function set($key, $value)
	{
		$this->connect();

		//log_file('Mem save: '.$key.' = '.bin2hex($value));

		return $this->mc->set($key, $value, 900);
	}

	public function get($key, &$value)
	{
		$this->connect();

		$value = $this->mc->get($key);

		if($this->mc->getResultCode() == Memcached::RES_NOTFOUND)
		{
			//log_file('Mem load: '.$key.' - NOT FOUND');
			return FALSE;
		}

		//log_file('Mem load: '.$key.' = '.bin2hex($value));

		return TRUE;
	}

	public function flush()
	{
		$this->connect();

		//log_file('Mem flush');

		return $this->mc->flush();
	}
}
