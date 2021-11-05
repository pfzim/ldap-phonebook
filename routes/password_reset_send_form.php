<?php

function password_reset_send_form(&$core, $params, $post_data)
{
	$result_json = array(
		'code' => 0,
		'message' => '',
		'title' => LL('ResetPassword'),
		'action' => 'password_reset_send',
		'fields' => array(
			array(
				'type' => 'string',
				'name' => 'mail',
				'title' => LL('Mail').'*',
				'value' => ''
			)
		)
	);

	echo json_encode($result_json);
}
