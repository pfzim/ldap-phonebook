<?php

function user_activate(&$core, $params, $post_data)
{
	$user_id = intval(@$post_data['id']);

	assert_permission_ajax(0, RB_ACCESS_EXECUTE);

	if(!$user_id)
	{
		echo '{"code": 1, "message": "Undefinded user ID"}';
		return;
	}

	if(!$core->UserAuth->activate_user_ex($user_id))
	{
		echo '{"code": 1, "message": "Failed activate"}';
		return;
	}

	log_db('Activated user', '{id='.$user_id.'}', 0);
	echo '{"code": 0, "id": '.$user_id.', "message": "'.LL('UserActivated').'"}';
}
