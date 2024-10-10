<?php

function setting_get(&$core, $params, $post_data)
{
	$user_id = intval(@$params[1]);
	$setting_key = @$params[2];

	assert_permission_ajax(0, USER_ACCESS_ADMIN);

	if($user_id)
	{
		$setting_value = $core->Config->get_user($setting_key, '');
	}
	else
	{
		$setting_value = $core->Config->get_global($setting_key, '');
	}

	$result_json = array(
		'code' => 0,
		'message' => '',
		'title' => LL('Edit'),
		'action' => 'setting_save',
		'fields' => array(
			array(
				'type' => 'hidden',
				'name' => 'uid',
				'value' => $user_id
			),
			array(
				'type' => 'readonly',
				'name' => 'key',
				'title' => LL('Parameter').'*',
				'value' => $setting_key
			),
			array(
				'type' => preg_match('/_json$/i', $setting_key) ? 'text' : 'string',
				'name' => 'value',
				'title' => LL('Value').'*',
				'value' => $setting_value
			)
		)
	);

	echo json_encode($result_json);
}
