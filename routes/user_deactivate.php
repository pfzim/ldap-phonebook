<?php

function user_deactivate(&$core, $params, $post_data)
{
	$user_id = intval(@$post_data['id']);

	assert_permission_ajax(0, RB_ACCESS_EXECUTE);

	if(!$user_id)
	{
		echo '{"code": 1, "message": "Undefinded user ID"}';
		return;
	}

	if(!$core->UserAuth->deactivate_user_ex($user_id))
	{
		echo '{"code": 1, "message": "Failed deactivate"}';
		return;
	}

	log_db('Deactivated user', '{id='.$user_id.'}', 0);
	echo '{"code": 0, "id": '.$user_id.', "message": "'.LL('UserDeactivated').'"}';
}
