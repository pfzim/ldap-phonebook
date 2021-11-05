<?php

function permission_get(&$core, $params, $post_data)
{
	$id = @$params[1];

	assert_permission_ajax(0, RB_ACCESS_EXECUTE);

	if(!$core->db->select_assoc_ex($permission, rpv("SELECT m.`id`, m.`oid`, m.`sid`, m.`dn`, m.`allow_bits` FROM `@access` AS m WHERE m.`id` = # LIMIT 1", $id)))
	{
		echo '{"code": 1, "message": "Failed get permissions"}';
		return;
	}

	if(defined('LDAP_USE_SID') && LDAP_USE_SID)
	{
		if($core->LDAP->search($records, '(&(objectCategory=group)(objectSID='.$permission[0]['sid'].'))', array('distinguishedName')) != 1)
		{
			echo '{"code": 1, "message": "Failed get DN by SID"}';
			return;
		}

		$group_dn = $records[0]['distinguishedName'][0];
	}
	else
	{
		$group_dn = $permission[0]['dn'];
	}

	$result_json = array(
		'code' => 0,
		'message' => '',
		'title' => LL('EditPermissions'),
		'action' => 'permission_save',
		'fields' => array(
			array(
				'type' => 'hidden',
				'name' => 'id',
				'value' => $permission[0]['id']
			),
			array(
				'type' => 'hidden',
				'name' => 'pid',
				'value' => $permission[0]['oid']
			),
			array(
				'type' => 'string',
				'name' => 'dn',
				'title' => LL('GroupDN').'*',
				'value' => $group_dn,
				'placeholder' => 'CN=WebSCO access group,OU=Access groups,DC=domain,DC=local',
				'autocomplete' => 'complete_group'
			),
			array(
				'type' => 'flags',
				'name' => 'allow_bits',
				'title' => LL('AllowRights'),
				'value' => ord($permission[0]['allow_bits'][0]) | (ord($permission[0]['allow_bits'][1]) << 8) | (ord($permission[0]['allow_bits'][2]) << 16) | (ord($permission[0]['allow_bits'][3]) << 24),
				'list' => array(LL('List'), LL('Execute'))
			),
			array(
				'type' => 'flags',
				'name' => 'apply_to_childs',
				'title' => LL('ApplyToChilds'),
				'value' => 0,
				'list' => array(LL('ApplyToChilds'), LL('ReplaceChilds'))
			),
		)
	);

	echo json_encode($result_json);
}
