<?php

function contacts_purge_deleted(&$core, $params, $post_data)
{
	assert_permission_ajax(0, PB_ACCESS_ADMIN);	// level 0 having Write access mean admin

	$core->db->put(rpv('DELETE FROM `@contacts` WHERE (`flags` & ({%PB_CONTACT_AD_DELETED}))'));

	echo '{"code": 0, "message": "Deleted contacts"}';
}
