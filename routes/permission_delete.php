<?php

function permission_delete(&$core, $params, $post_data)
{
	$id = intval(@$post_data['id']);

	assert_permission_ajax(0, PB_ACCESS_ADMIN);

	if(!$id || !$core->db->put(rpv("DELETE FROM `@access` WHERE `id` = # LIMIT 1", $id)))
	{
		echo '{"code": 1, "message": "Failed delete"}';
		exit;
	}

	log_db('Deleted permission', '{id='.$id.'}', 0);

	echo '{"code": 0, "id": '.$id.', "message": "Permission deleted"}';
}
