<?php

function logon(&$core, $params, $post_data)
{
	if(!$core->UserAuth->logon(@$post_data['login'], @$post_data['passwd']))
	{
		$return_url = @$post_data['return'];
		$error_msg = LL('InvalidUserPasswd');
		include(TEMPLATES_DIR.'tpl.login.php');
		exit;
	}

	/*
	if(!$core->UserAuth->is_member(LDAP_ADMIN_GROUP_DN))
	{
		$core->UserAuth->logoff();
		$error_msg = 'Access denied!';
		include(TEMPLATES_DIR.'tpl.login.php');
		exit;
	}
	*/
	
	if(!empty($post_data['return']))
	{
		header('Location: '.$post_data['return']);
	}
	else
	{
		header('Location: '.WEB_LINK_PREFIX);
	}
}
