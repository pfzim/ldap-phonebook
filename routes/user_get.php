<?php

function user_get(&$core, $params, $post_data)
{
	$user_id = intval(@$params[1]);

	assert_permission_ajax(0, PB_ACCESS_ADMIN);

	$login = '';
	$mail = '';
	$flags = 0;

	if($user_id)
	{
		if(!$core->db->select_assoc_ex($user_info, rpv("SELECT u.`login`, u.`mail`, u.`flags` FROM @users AS u WHERE u.`id` = # LIMIT 1", $user_id)))
		{
			echo '{"code": 1, "message": "Failed get permissions"}';
			return;
		}
		
		$login = @$user_info[0]['login'];
		$mail = @$user_info[0]['mail'];
		$flags = (intval(@$user_info[0]['flags']) & UA_ADMIN) ? 0x0001 : 0;
	}

	$result_json = array(
		'code' => 0,
		'message' => '',
		'title' => LL('EditUser'),
		'action' => 'user_save',
		'fields' => array(
			array(
				'type' => 'hidden',
				'name' => 'id',
				'value' => $user_id
			),
			array(
				'type' => 'string',
				'name' => 'login',
				'title' => LL('Login').'*',
				'value' => $login
			),
			array(
				'type' => 'string',
				'name' => 'mail',
				'title' => LL('Mail').'*',
				'value' => $mail,
				'autocomplete' => 'complete_mail'
			),
			array(
				'type' => 'flags',
				'name' => 'flags',
				'title' => LL('AllowRights'),
				'value' => $flags,
				'list' => array(LL('Admin'))
			)
		)
	);

	echo json_encode($result_json);
}
