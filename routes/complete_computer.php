<?php

function complete_computer(&$core, $params, $post_data)
{
	$search = @$post_data['search'];

	$result_json = array(
		'code' => 0,
		'message' => '',
		'list' => array()
	);

	if(defined('USE_LDAP') && USE_LDAP && !empty($search) && strlen($search) >= 3)
	{
		if($core->LDAP->search($result, '(&(objectClass=computer)(sAMAccountName='.ldap_escape($search, null, LDAP_ESCAPE_FILTER).'*))', array('cn')))
		{
			foreach($result as $row)
			{
				$result_json['list'][] = $row['cn'][0];
			}
		}
	}

	echo json_encode($result_json);
}
