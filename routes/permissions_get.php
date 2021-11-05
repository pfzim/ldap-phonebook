<?php

function permissions_get(&$core, $params, $post_data)
{
	$id = @$params[1];

	assert_permission_ajax(0, RB_ACCESS_EXECUTE);	// level 0 having access mean admin

	if(empty($id) || intval($id) == 0)
	{
		$current_section = array(
			'name' => 'Top level',
			'id' => 0,
			'flags' => 0
		);
	}
	else
	{
		$core->db->select_assoc_ex($folder, rpv('SELECT f.`id`, f.`name`, f.`flags` FROM `@runbooks_folders` AS f WHERE f.`id` = # ORDER BY f.`name`', $id));
		$current_section = &$folder[0];
	}

	$core->db->select_assoc_ex($permissions, rpv('SELECT a.`id`, a.`sid`, a.`dn`, a.`allow_bits` FROM `@access` AS a WHERE a.`oid` = # ORDER BY a.`dn`', $current_section['id']));

	$result_json = array(
		'code' => 0,
		'name' => $current_section['name'],
		'id' => $current_section['id'],
		'flags' => $current_section['flags'],
		'permissions' => array()
	);

	foreach($permissions as &$row)
	{
		$group_name = &$row['dn'];
		if(preg_match('/^..=([^,]+),/i', $group_name, $matches))
		{
			$group_name = &$matches[1];
		}

		$result_json['permissions'][] = array(
			'id' => &$row['id'],
			'group' => $group_name,
			'perms' => $core->UserAuth->permissions_to_string($row['allow_bits'])
		);
	}

	echo json_encode($result_json);
}
