<?php

function register(&$core, $params, $post_data)
{
	$result_json = array(
		'code' => 0,
		'message' => '',
		'errors' => array()
	);

	$user_id = intval(@$post_data['id']);
	$login = @$post_data['login'];
	$mail = @$post_data['mail'];
	$new_password = @$post_data['new_password'];
	$new_password2 = @$post_data['new_password2'];

	if(empty($login))
	{
		$result_json['code'] = 1;
		$result_json['errors'][] = array('name' => 'login', 'msg' => LL('ThisFieldRequired'));
	}

	if(empty($mail))
	{
		$result_json['code'] = 1;
		$result_json['errors'][] = array('name' => 'mail', 'msg' => LL('ThisFieldRequired'));
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
	elseif($user_id = $core->UserAuth->add($login, $new_password, $mail))
	{
		$html = <<<'EOT'
			<html>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
				</head>
				<body>
EOT;

		$html .= 'New request to register an administrator.<br />';
		$html .= '<br />';
		$html .= 'Login: '.htmlspecialchars($login).'<br />';
		$html .= 'E-Mail: '.htmlspecialchars($mail).'<br />';
		$html .= '<br />';
		$html .= 'To approve new registered user follow this link: <a href="'.WEB_LINK_EXTERNAL.'register_approve_form/'.$user_id.'">'.WEB_LINK_EXTERNAL.'register_approve_form/'.$user_id.'</a>';

		$html .= '</body></html>';

		$plain = 'To approve new registered user follow this link: '.WEB_LINK_EXTERNAL.'register_approve_form/'.$user_id;

		$core->Mailer->send_mail(array(MAIL_ADMIN), LL('ApproveRequestSubject'), $html, $plain);

		log_db('Registered new user', '{id='.$user_id.',login="'.$login.'"}', 0);
		$result_json['message'] = LL('UserRegistered');
	}
	else
	{
		$result_json['code'] = 1;
		$result_json['message'] = LL('Error').': '.$core->get_last_error();
	}

	//log_file('Password changed: '.json_encode($result_json, JSON_UNESCAPED_UNICODE));
	echo json_encode($result_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
