<?php

function logoff(&$core, $params, $post_data)
{
	$return_url = '';

	$core->UserAuth->logoff();

	include(TEMPLATES_DIR.'tpl.login.php');
}
