<?php
/*
    Router class - just router
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

class Router
{
	private $core = NULL;
	private $routes = array();
	private $exception_handler = NULL;
	private $exception_handler_ajax = NULL;

	function __construct(&$core)
	{
		$this->core = &$core;
		$this->routes = array();
		$this->exception_handler = NULL;
		$this->exception_handler_ajax = NULL;
	}
	
	public function add_route($action, $function, $is_ajax = FALSE)
	{
		$this->routes[$action] = array(
			'func' => $function,
			'ajax' => $is_ajax
		);
	}

	public function set_exception_handler_regular($function)
	{
		$this->exception_handler = $function;
	}

	public function set_exception_handler_ajax($function)
	{
		$this->exception_handler_ajax = $function;
	}

	public function process($uri, $post_data)
	{
		$params = explode('/', $uri);

		if(!isset($this->routes[$params[0]]))
		{
			$route = reset($this->routes);
			//$this->core->error('Unknown route');
			//return;
		}
		else
		{
			$route = $this->routes[$params[0]];
		}

		if($route['ajax'])
		{
			header('Content-Type: text/plain; charset=utf-8');

			if($this->exception_handler_ajax)
			{
				set_exception_handler($this->exception_handler_ajax);
			}
		}
		else
		{
			header('Content-Type: text/html; charset=utf-8');

			if($this->exception_handler)
			{
				set_exception_handler($this->exception_handler);
			}
		}
	
		if(!function_exists($route['func']))
		{
			$filepath = ROUTES_DIR.$route['func'].'.php';
			if(!file_exists($filepath))
			{
				$this->core->error('ERROR: Route file '.$filepath.' not found!');
				return NULL;
			}

			require_once($filepath);
		}

		call_user_func_array($route['func'], array(&$this->core, $params, $post_data));
	}
}
