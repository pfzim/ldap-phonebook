<?php

function contacts_purge_deleted(&$core, $params, $post_data)
{
	assert_permission_ajax(0, PB_ACCESS_ADMIN);	// level 0 having Write access mean admin

	$i = 0;

	$core->db->select_assoc_ex($result, rpv('SELECT c.`id` FROM `@contacts` AS c WHERE (c.`flags` & ({%PB_CONTACT_AD_DELETED}))'));
	foreach($result as &$row)
	{
		$filename = ROOT_DIR.'photos'.DIRECTORY_SEPARATOR.'t'.$row['id'].'.jpg';

		if(file_exists($filename))
		{
			unlink($filename);
		}

		$core->db->put(rpv('DELETE FROM `@contacts` WHERE `id` = #', $row['id']));
		$i++;
	}

	echo '{"code": 0, "message": "Deleted '.$i.' contacts"}';
}
