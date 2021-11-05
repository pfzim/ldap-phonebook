<?php

function password_change_form(&$core, $params, $post_data)
{
	$result_json = array(
		'code' => 0,
		'message' => '',
		'title' => LL('ChangePassword'),
		'action' => 'password_change',
		'fields' => array(
			array(
				'type' => 'password',
				'name' => 'old_password',
				'title' => LL('OldPassword').'*',
				'value' => ''
			),
			array(
				'type' => 'password',
				'name' => 'new_password',
				'title' => LL('NewPassword').'*',
				'value' => ''
			),
			array(
				'type' => 'password',
				'name' => 'new_password2',
				'title' => LL('NewPasswordAgain').'*',
				'value' => ''
			),
		)
	);

	echo json_encode($result_json);
}
