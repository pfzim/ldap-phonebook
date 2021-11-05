<?php

function register_approve(&$core, $params, $post_data)
{
	if(!$core->UserAuth->check_permission(0, RB_ACCESS_EXECUTE))
	{
		$error_msg = LL('AccessDeniedToSection').' 0 '.LL('forUser').' '.$core->UserAuth->get_login().'!';
		include(TEMPLATES_DIR.'tpl.message.php');
		exit;
	}

	$user_id = intval(@$post_data['uid']);
	
	$user_info = $core->UserAuth->get_user_info_ex($user_id);
	if($user_info)
	{
		if($core->UserAuth->activate($user_id, $user_info['login'], $mail))
		{

			$html = <<<'EOT'
				<html>
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
					</head>
					<body>
EOT;

			$html .= 'Welcome! You account activated';

			$html .= '</body></html>';

			$plain = 'Welcome! You account activated';

			$core->Mailer->send_mail(array($mail), LL('WelcomeSubject'), $html, $plain);

			$error_msg = LL('UserApproved');
		}
		else
		{
			$error_msg = LL('Error');
		}
	}
	else
	{
		$error_msg = LL('Error');
	}

	include(TEMPLATES_DIR.'tpl.message.php');
}
