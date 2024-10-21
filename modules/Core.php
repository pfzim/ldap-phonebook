<?php
/*
    Core class - This class is designed to provide communication between
	             modules
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

/**
	This class is designed to provide communication between modules.
*/

class Core
{
	private $error_msg = '';
	private $rise_exception = FALSE;
	private $config = NULL;
	private $loaded_classes = array();

	function __construct($rise_exception = FALSE)
	{
		$this->config = NULL;
		$this->error_msg = '';
		$this->loaded_classes = array();
		$this->rise_exception = $rise_exception;
	}
	
    /*
	public function __call($name, $arguments)
	{
		$this->load($name);
        return call_user_func_array($this->$name, $arguments);
	}
	*/

	public function load_ex($name, $module)
	{
		if(isset($this->loaded_classes[$name]))
		{
			return $this->loaded_classes[$name];
		}

		$filepath = MODULES_DIR.$module.'.php';
		if(!file_exists($filepath))
		{
			$this->error('ERROR: Module '.$filepath.' not found!');
			return NULL;
		}
		
		require_once($filepath);

		$this->loaded_classes[$name] = new $module($this);

		return $this->loaded_classes[$name];
	}

	public function load($module)
	{
		return $this->load_ex($module, $module);
	}

	public function __get($module)
	{
		return $this->load_ex($module, $module);
	}

	public function get_config($name)
	{
		if(!$this->config)
		{
			$json_raw = file_get_contents(ROOT_DIR.'config.json');
			$this->config = json_decode($json_raw, TRUE);
		}
		
		if(!isset($this->config[$name]))
		{
			return NULL;
		}

		return $this->config[$name];
	}

	public function get_last_error()
	{
		return $this->error_msg;
	}

	public function error($str)
	{
		$this->error_ex($str, $this->rise_exception);
	}

	public function error_ex($str, $rise_exception)
	{
		if($rise_exception)
		{
			throw new Exception(__CLASS__.': '.$str);
		}
		else
		{
			$this->error_msg = $str;
		}
	}
}
