<?php

function password_change(&$core, $params, $post_data)
{
	$result_json = array(
		'code' => 0,
		'message' => '',
		'errors' => array()
	);

	$old_password = @$post_data['old_password'];
	$new_password = @$post_data['new_password'];
	$new_password2 = @$post_data['new_password2'];

	if($core->UserAuth->is_ldap_user() || !$core->UserAuth->get_id())
	{
		throw new Exception('Something gone wrong. User is not logging in or user not local!');
	}

	if(empty($old_password))
	{
		$result_json['code'] = 1;
		$result_json['errors'][] = array('name' => 'old_password', 'msg' => LL('ThisFieldRequired'));
	}
	elseif(!$core->UserAuth->check_password($old_password))
	{
		$result_json['code'] = 1;
		$result_json['errors'][] = array('name' => 'old_password', 'msg' => LL('InvalidPassword'));
	}

	if(empty($new_password))
	{
		$result_json['code'] = 1;
		$result_json['errors'][] = array('name' => 'new_password', 'msg' => LL('ThisFieldRequired'));
	}

	if(empty($new_password2))
	{
		$result_json['code'] = 1;
		$result_json['errors'][] = array('name' => 'new_password2', 'msg' => LL('ThisFieldRequired'));
	}
	elseif(strcmp($new_password, $new_password2) !== 0)
	{
		$result_json['code'] = 1;
		$result_json['errors'][] = array('name' => 'new_password2', 'msg' => LL('PasswordsNotMatch'));
	}

	if($result_json['code'])
	{
		$result_json['message'] = LL('NotAllFilled');
	}
	elseif($core->UserAuth->change_password($new_password))
	{
		$result_json['message'] = LL('PasswordChanged');
	}
	else
	{
		$result_json['message'] = LL('UnknownError');
	}

	//log_file('Password changed: '.json_encode($result_json, JSON_UNESCAPED_UNICODE));
	echo json_encode($result_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
