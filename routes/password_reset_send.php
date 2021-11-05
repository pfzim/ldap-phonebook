<?php

function password_reset_send(&$core, $params, $post_data)
{
	$result_json = array(
		'code' => 0,
		'message' => '',
		'errors' => array()
	);

	$mail = @$post_data['mail'];
	$user_id = 0;

	if(empty($mail))
	{
		$result_json['code'] = 1;
		$result_json['errors'][] = array('name' => 'mail', 'msg' => LL('ThisFieldRequired'));
	}
	else
	{
		$user_id = $core->UserAuth->find_user_by_mail($mail);
	}

	if(!$user_id || !$core->UserAuth->make_reset_token($user_id, $reset_token))
	{
		$result_json['code'] = 1;
		$result_json['errors'][] = array('name' => 'mail', 'msg' => LL('UserNotFound'));
	}

	$html = <<<'EOT'
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	</head>
	<body>
EOT;

	$html .= 'To reset password follow this link: <a href="'.WEB_LINK_EXTERNAL.'password_reset_form/'.$user_id.'/'.$reset_token.'">'.WEB_LINK_EXTERNAL.'password_reset_form/'.$user_id.'/'.$reset_token.'</a>';

	$html .= '</body></html>';

	$plain = 'To reset password follow this link: '.WEB_LINK_EXTERNAL.'password_reset_form/'.$user_id.'/'.$reset_token;

	if($result_json['code'])
	{
		$result_json['message'] = LL('NotAllFilled');
	}
	elseif($core->Mailer->send_mail(array($mail), LL('ResetPasswordSubject'), $html, $plain))
	{
		log_db('Send mail to reset password', '{id='.$user_id.'}', 0);
		$result_json['message'] = LL('MailWasSent');
	}
	else
	{
		$result_json['code'] = 1;
		$result_json['message'] = LL('UnknownError');
	}

	//log_file('Password changed: '.json_encode($result_json, JSON_UNESCAPED_UNICODE));
	echo json_encode($result_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
