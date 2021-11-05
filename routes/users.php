<?php

function users(&$core, $params, $post_data)
{
	if(!$core->UserAuth->check_permission(0, RB_ACCESS_EXECUTE))
	{
		$error_msg = LL('AccessDeniedToSection').' 0 '.LL('forUser').' '.$core->UserAuth->get_login().'!';
		include(TEMPLATES_DIR.'tpl.message.php');
		exit;
	}

	$core->db->select_assoc_ex($users, rpv('
		SELECT
			u.`id`,
			u.`login`,
			u.`mail`,
			u.`flags`
		FROM @users AS u
		WHERE
			(u.`flags` & ({%UA_LDAP} | {%UA_DELETED})) = 0
		ORDER BY u.`login`
	'));

	include(TEMPLATES_DIR.'tpl.users.php');
}
