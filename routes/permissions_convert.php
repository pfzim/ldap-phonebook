<?php

function permissions_convert(&$core, $params, $post_data)
{
	assert_permission_ajax(0, RB_ACCESS_EXECUTE);

	if(!$core->db->select_assoc_ex($permissions, rpv("SELECT m.`id`, m.`oid`, m.`sid`, m.`dn`, m.`allow_bits` FROM `@access` AS m", $id)))
	{
		echo '{"code": 1, "message": "Failed get permissions"}';
		exit;
	}
	
	$i = 0;

	foreach($permissions as $pemission)
	{
		if($core->LDAP->search($result, '(&(objectCategory=group)(distinguishedName='.ldap_escape($pemission['dn'], null, LDAP_ESCAPE_FILTER).'))', array('distinguishedName', 'objectSID')) == 1)
		{
			$v_sid = bin_to_str_sid($result[0]['objectSid'][0]);

			if($core->db->put(rpv("UPDATE `@access` SET `sid` = ! WHERE `id` = # LIMIT 1",
				$v_sid,
				$pemission['id']
			)))
			{
				$i++;
			}
		}
	}

	echo '{"code": 0, "message": "Converting done! (count: '.$i.')"}';
}
