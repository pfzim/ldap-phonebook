<?php
/*
    Config class - Work with config.
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

class Config
{
	private $core = NULL;
	private $config = array();

	function __construct(&$core)
	{
		$this->core = &$core;
		$this->config = array();
	}

	private function load($uid)
	{
		if($this->core->db->select_ex($cfg, rpv('SELECT m.`name`, m.`value` FROM @config AS m WHERE m.`uid` = #', $uid)))
		{
			$this->config[$uid] = array();

			foreach($cfg as &$row)
			{
				$this->config[$uid][$row[0]] = $row[1];
			}
		}
	}

	private function set_ex($uid, $key, $value)
	{
		$this->core->db->put(rpv('INSERT INTO @config (`uid`, `name`, `value`) VALUES ({d0}, {s1}, {s2}) ON DUPLICATE KEY UPDATE `value` = {s2}', $uid, $key, $value));

		if(!isset($this->config[$uid]))
		{
			$this->load($uid);
		}
		else
		{
			$this->config[$uid][$key] = $value;
		}
	}

	private function get_ex($uid, $key, $def_value)
	{
		if(!isset($this->config[$uid]))
		{
			$this->load($uid);
		}

		if(!isset($this->config[$uid][$key]))
		{
			return $def_value;
		}

		return $this->config[$uid][$key];
	}

	public function set_global($key, $value)
	{
		$this->set_ex(0, $key, $value);
	}

	public function get_global($key, $def_value)
	{
		return $this->get_ex(0, $key, $def_value);
	}

	public function set_user($key, $value)
	{
		$uid = $this->core->UserAuth->get_id();
		if(!$uid)
		{
			$this->core->error('Algorithm error. User not logged in!');
			return;
		}

		$this->set_ex($uid, $key, $value);
	}

	public function get_user($key, $def_value)
	{
		$uid = $this->core->UserAuth->get_id();
		if(!$uid)
		{
			$this->core->error('Algorithm error. User not logged in!');
			return NULL;
		}

		return $this->get_ex($this->core->UserAuth->get_id(), $key, $def_value);
	}
}
