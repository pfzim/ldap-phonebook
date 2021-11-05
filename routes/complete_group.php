<?php

function complete_group(&$core, $params, $post_data)
{
	$search = @$post_data['search'];

	$result_json = array(
		'code' => 0,
		'message' => '',
		'list' => array()
	);
	
	if(defined('USE_LDAP') && USE_LDAP && !empty($search) && strlen($search) >= 3)
	{
		if($core->LDAP->search($result, '(&(objectCategory=group)(cn='.ldap_escape($search, null, LDAP_ESCAPE_FILTER).'*))', array('distinguishedName')))
		{
			log_file(print_r($result, TRUE));
			foreach($result as $row)
			{
				$result_json['list'][] = $row['distinguishedName'][0];
			}
		}
	}

	echo json_encode($result_json);
}
