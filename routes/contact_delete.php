<?php

function contact_delete(&$core, $params, $post_data)
{
	$id = intval(@$post_data['id']);

	assert_permission_ajax(0, PB_ACCESS_ADMIN);	// level 0 having Write access mean admin

	$core->db->put(rpv('DELETE FROM `@contacts` WHERE `id` = # AND `adid` = \'\' LIMIT 1', $id));

	$filename = ROOT_DIR.'photos'.DIRECTORY_SEPARATOR.'t'.$id.'.jpg';

	if(file_exists($filename))
	{
		unlink($filename);
	}

	echo '{"code": 0, "message": "Deleted (ID '.$id.')"}';
}

