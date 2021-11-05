<?php

function hello(&$core, $params, $post_data)
{
	$s_user_name = trim(@$post_data['user']);
	$s_comp_name = trim(@$post_data['comp']);

	if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = @$_SERVER['REMOTE_ADDR'];
	}

	if(empty($s_user_name))
	{
		echo '{"code": 1, "message": "Undefined user name"}';
		exit;
	}

	$core->db->put(rpv('INSERT INTO `@handshake` (`user`, `date`, `computer`, `ip`) VALUES (!, NOW(), !, !)', $s_user_name, $s_comp_name, $ip));

	echo '{"code": 0, "message": "HI"}';
}

