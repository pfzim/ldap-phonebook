<?php

function contact_show(&$core, $params, $post_data)
{
	$id = intval(@$post_data['id']);

	assert_permission_ajax(0, PB_ACCESS_ADMIN);	// level 0 having Write access mean admin

	$core->db->put(rpv('UPDATE `@contacts` SET `flags` = (`flags` | {%PB_CONTACT_VISIBLE}) WHERE `id` = # LIMIT 1', $id));

	echo '{"code": 0, "message": "Successful show (ID '.$id.')"}';
}

