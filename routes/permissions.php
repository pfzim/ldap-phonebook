<?php

function permissions(&$core, $params, $post_data)
{
	//$id = @$params[1];
	$id = 0;

	if(!$core->UserAuth->check_permission(0, RB_ACCESS_EXECUTE))
	{
		$error_msg = LL('AccessDeniedToSection').' 0 '.LL('forUser').' '.$core->UserAuth->get_login().'!';
		include(TEMPLATES_DIR.'tpl.message.php');
		exit;
	}

	if(empty($id) || intval($id) == 0)
	{
		$current_folder = array(
			'name' => LL('RootLevel'),
			'id' => 0,
			'pid' => '00000000-0000-0000-0000-000000000000',
			'guid' => '00000000-0000-0000-0000-000000000000',
			'flags' => 0,
			'childs' => NULL
		);
	}

	$core->db->select_assoc_ex($permissions, rpv('SELECT a.`id`, a.`oid`, a.`dn`, a.`allow_bits` FROM `@access` AS a WHERE a.`oid` = # ORDER BY a.`dn`', $current_folder['id']));

	include(TEMPLATES_DIR.'tpl.permissions.php');
}
