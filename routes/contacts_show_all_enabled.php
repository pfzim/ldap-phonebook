<?php

function contacts_show_all_enabled(&$core, $params, $post_data)
{
	assert_permission_ajax(0, PB_ACCESS_ADMIN);	// level 0 having Write access mean admin

	$core->db->put(rpv('UPDATE `@contacts` SET `flags` = (`flags` | {%PB_CONTACT_VISIBLE}) WHERE adid = \'\' AND (`flags` & ({%PB_CONTACT_AD_DELETED} | {%PB_CONTACT_AD_DISABLED} | {%PB_CONTACT_VISIBLE})) = 0'));

	echo '{"code": 0, "message": "Showed all existing and not disabled AD contacts"}';
}
