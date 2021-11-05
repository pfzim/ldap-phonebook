<?php

function register_decline(&$core, $params, $post_data)
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
		if($core->UserAuth->delete_user_ex($user_id))
		{
			$error_msg = LL('UserDeclined');
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
