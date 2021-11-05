<?php

function permission_save(&$core, $params, $post_data)
{
	$result_json = array(
		'code' => 0,
		'message' => '',
		'errors' => array()
	);

	$v_id = intval(@$post_data['id']);
	$v_pid = intval(@$post_data['pid']);
	$v_dn = trim(@$post_data['dn']);
	$v_allow = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
	$v_apply_to_childs = intval(@$post_data['apply_to_childs'][0]) || intval(@$post_data['apply_to_childs'][1]);
	$v_replace_childs = intval(@$post_data['apply_to_childs'][1]);

	if(isset($post_data['allow_bits']))
	{
		foreach($post_data['allow_bits'] as $bit => $bit_value)
		{
			if(intval($bit_value))
			{
				set_permission_bit($v_allow, intval($bit) + 1);
			}
		}
	}

	assert_permission_ajax(0, RB_ACCESS_EXECUTE);	// level 0 having access mean admin

	if(empty($v_dn))
	{
		$result_json['code'] = 1;
		$result_json['errors'][] = array('name' => 'dn', 'msg' => LL('ThisFieldRequired'));
	}

	if($result_json['code'])
	{
		$result_json['message'] = LL('NotAllFilled');
		echo json_encode($result_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
		exit;
	}

	if($core->LDAP->search($result, '(&(objectCategory=group)(distinguishedName='.ldap_escape($v_dn, null, LDAP_ESCAPE_FILTER).'))', array('distinguishedName', 'objectSID')) == 1)
	{
		$v_sid = bin_to_str_sid($result[0]['objectSid'][0]);
		//log_file(print_r($result, true));
	}

	if(!$v_id)
	{
		if($core->db->put(rpv("INSERT INTO `@access` (`oid`, `sid`, `dn`, `allow_bits`) VALUES (#, !, !, !)",
			$v_pid,
			$v_sid,
			$v_dn,
			$v_allow
		)))
		{
			$v_id = $core->db->last_id();

			log_db('Added permission', 'id='.$v_id.';oid='.$v_pid.';dn='.$v_dn.';sid='.$v_sid.';perms='.$core->UserAuth->permissions_to_string($v_allow), 0);

			$result_json['id'] = $v_id;
			$result_json['pid'] = $v_pid;
			$result_json['message'] = LL('Added').' (ID '.$v_id.')';
		}
		else
		{
			$result_json['code'] = 1;
			$result_json['message'] = 'ERROR: '.$core->get_last_error();
		}
	}
	else
	{
		if($core->db->put(rpv("UPDATE `@access` SET `sid` = !, `dn` = !, `allow_bits` = ! WHERE `id` = # AND `oid` = # LIMIT 1",
			$v_sid,
			$v_dn,
			$v_allow,
			$v_id,
			$v_pid
		)))
		{
			log_db('Updated permission', 'id='.$v_id.';oid='.$v_pid.';dn='.$v_dn.';sid='.$v_sid.';perms='.$core->UserAuth->permissions_to_string($v_allow), 0);

			$result_json['id'] = $v_id;
			$result_json['pid'] = $v_pid;
			$result_json['message'] = LL('Updated').' (ID '.$v_id.')';
		}
		else
		{
			$result_json['code'] = 1;
			$result_json['message'] = 'ERROR: '.$core->get_last_error();
		}
	}

	if($result_json['code'])
	{
		echo json_encode($result_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
		exit;
	}

	if($v_apply_to_childs)
	{
		function permissions_apply_to_childs($parent_guid, $v_dn, $v_allow, $replace)
		{
			global $core;
			$childs = 0;

			log_file('Apply to childs of ID: '.$parent_guid);
			if($core->db->select_assoc_ex($folders, rpv('SELECT f.`id`, f.`guid` FROM `@runbooks_folders` AS f WHERE (f.`flags` & 0x0001) = 0 AND f.`pid` = !', $parent_guid)))
			{
				foreach($folders as &$folder)
				{
					//log_file('  Folder ID: '.$folder['id'].', GUID: '.$folder['guid'].', Name: '.$folder['name']);

					if($core->db->select_assoc_ex($permissions, rpv('SELECT a.`id`, a.`allow_bits` FROM `@access` AS a WHERE a.`oid` = # AND a.`dn` = !', $folder['id'], $v_dn)))
					{
						if($replace)
						{
							$bits = $v_allow;
						}
						else
						{
							$bits = $core->UserAuth->merge_permissions($v_allow, $permissions[0]['allow_bits']);
						}
						$core->db->put(rpv("UPDATE `@access` SET `allow_bits` = ! WHERE `id` = # AND `oid` = # LIMIT 1", $bits, $permissions[0]['id'], $folder['id']));
						//log_file('  UPDATE');
					}
					else
					{
						$core->db->put(rpv("INSERT INTO `@access` (`oid`, `dn`, `allow_bits`) VALUES (#, !, !)", $folder['id'], $v_dn, $v_allow));
						//log_file('  INSERT');
					}

					$childs += permissions_apply_to_childs($folder['guid'], $v_dn, $v_allow, $replace) + 1;
				}
			}

			return $childs;
		}

		$folder_guid = NULL;
		if($v_pid == 0)
		{
			$folder_guid = '00000000-0000-0000-0000-000000000000';
		}
		elseif($core->db->select_assoc_ex($folders, rpv('SELECT f.`guid` FROM `@runbooks_folders` AS f WHERE (f.`flags` & 0x0001) = 0 AND f.`id` = #', $v_pid)))
		{
			$folder_guid = $folders[0]['guid'];
		}

		if($folder_guid)
		{
			$result_json['childs'] = permissions_apply_to_childs($folder_guid, $v_dn, $v_allow, $v_replace_childs);
		}
	}

	if(defined('USE_MEMCACHED') && USE_MEMCACHED)
	{
		$core->Mem->flush();
	}

	//log_file('Save permissions: '.json_encode($result_json, JSON_UNESCAPED_UNICODE));
	echo json_encode($result_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
