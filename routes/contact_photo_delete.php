<?php

function contact_photo_delete(&$core, $params, $post_data)
{
	$id = intval(@$post_data['id']);

	assert_permission_ajax(0, PB_ACCESS_ADMIN);	// level 0 having Write access mean admin

	if(!$id)
	{
		echo '{"code": 1, "message": "Invalid identifier"}';
		exit;
	}

	$core->db->put(rpv('UPDATE `@contacts` SET `flags` = (`flags` & ~{%PB_CONTACT_WITH_PHOTO}) WHERE `id` = # LIMIT 1', $id));

	echo '{"code": 0, "id": '.$id.', "message": "Photo deleted (ID '.$id.')"}';
}

