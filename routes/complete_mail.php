<?php

function complete_mail(&$core, $params, $post_data)
{
	$search = @$post_data['search'];

	$result_json = array(
		'code' => 0,
		'message' => '',
		'list' => array()
	);
	
	if(defined('USE_LDAP') && USE_LDAP && !empty($search) && strlen($search) >= 3)
	{
		if($core->LDAP->search($result, '(&(objectCategory=person)(objectClass=user)(mail='.ldap_escape($search, null, LDAP_ESCAPE_FILTER).'*))', array('mail')))
		{
			foreach($result as $row)
			{
				$result_json['list'][] = $row['mail'][0];
			}
		}
	}

	echo json_encode($result_json);
}
