<?php

function login(&$core, $params, $post_data)
{
	if(!empty($params[0]) && $params[0] == 'login')
	{
		$return_url = '';
	}
	else
	{
		$return_url = $_SERVER['REQUEST_URI'];
	}

	$core->UserAuth->logoff();

	include(TEMPLATES_DIR.'tpl.login.php');
}
