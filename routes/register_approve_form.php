<?php

function register_approve_form(&$core, $params, $post_data)
{
	if(!$core->UserAuth->get_id() || $core->UserAuth->is_ldap_user())
	{
		$error_msg = 'Only local administrators can approve!';
		include(TEMPLATES_DIR.'tpl.message.php');
		exit;
	}

	$user_id = @$params[1];
	
	$user_info = $core->UserAuth->get_user_info_ex($user_id);

	include(TEMPLATES_DIR.'tpl.register-approve-form.php');
}
