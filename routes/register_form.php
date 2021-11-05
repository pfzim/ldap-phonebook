<?php

function register_form(&$core, $params, $post_data)
{
	$result_json = array(
		'code' => 0,
		'message' => '',
		'title' => LL('Register'),
		'action' => 'register',
		'fields' => array(
			array(
				'type' => 'string',
				'name' => 'login',
				'title' => LL('Login').'*',
				'value' => ''
			),
			array(
				'type' => 'string',
				'name' => 'mail',
				'title' => LL('Mail').'*',
				'value' => ''
			),
			array(
				'type' => 'password',
				'name' => 'new_password',
				'title' => LL('NewPassword').'*'
			),
			array(
				'type' => 'password',
				'name' => 'new_password2',
				'title' => LL('NewPasswordAgain').'*'
			)
		)
	);

	echo json_encode($result_json);
}
