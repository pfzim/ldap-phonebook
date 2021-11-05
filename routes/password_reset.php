<?php

function password_reset(&$core, $params, $post_data)
{
	$core->UserAuth->logoff();

	$new_password = @$post_data['new_password'];
	$new_password2 = @$post_data['new_password2'];
	$user_id = intval(@$post_data['uid']);
	$reset_token = @$post_data['reset_token'];

	if(empty($reset_token))
	{
		$error_msg = LL('UserNotFound');
		include(TEMPLATES_DIR.'tpl.password-reset-form.php');
		return;
	}
	elseif(!$user_id)
	{
		$error_msg = LL('UserNotFound');
		include(TEMPLATES_DIR.'tpl.password-reset-form.php');
		return;
	}
	elseif(empty($new_password) || empty($new_password2))
	{
		$error_msg = LL('NotAllFilled');
		include(TEMPLATES_DIR.'tpl.password-reset-form.php');
		return;
	}

	if(strcmp($new_password, $new_password2) !== 0)
	{
		$error_msg = LL('PasswordsNotMatch');
		include(TEMPLATES_DIR.'tpl.password-reset-form.php');
		return;
	}

	if(!$core->UserAuth->reset_password($user_id, $reset_token, $new_password))
	{
		$error_msg = LL('UnknownError');
		include(TEMPLATES_DIR.'tpl.password-reset-form.php');
		return;
	}

	header('Location: '.WEB_LINK_PREFIX);
}
