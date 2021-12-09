<?php

function contacts_sync(&$core, $params, $post_data)
{
	assert_permission_ajax(0, PB_ACCESS_ADMIN);	// level 0 having Write access mean admin

	$upload_dir = ROOT_DIR.'photos';

	$data = array();
	$count_updated = 0;
	$count_added = 0;
	$cookie = '';

	if(!defined('USE_LDAP') || !USE_LDAP)
	{
		echo '{"code": 0, "message": "Nothing to load. LDAP disabled in config file."}';
		return;
	}

	// mark all contacts as deleted before sync
	$core->db->put(rpv('UPDATE `@contacts` SET `flags` = (`flags` | {%PB_CONTACT_AD_DELETED}) WHERE `adid` <> \'\''));

	do
	{
		$result = ldap_search(
			$core->LDAP->get_link(),
			LDAP_BASE_DN,
			PB_LDAP_FILTER,
			['objectguid', 'samaccountname' , 'sn', 'initials', 'middleName', 'givenname', 'mail', 'department', 'company', 'title', 'telephonenumber', 'mobile', 'thumbnailphoto', 'useraccountcontrol'],
			0,
			0,
			0,
			LDAP_DEREF_NEVER,
			[['oid' => LDAP_CONTROL_PAGEDRESULTS, 'value' => ['size' => 200, 'cookie' => $cookie]]]
		);

		if($result === FALSE)
		{
			throw new Exception('ldap_search return error: '.ldap_error($core->LDAP->get_link()));
		}

		if(!ldap_parse_result($core->LDAP->get_link(), $result, $errcode , $matcheddn , $errmsg , $referrals, $controls))
		{
			throw new Exception('ldap_parse_result return error: '.ldap_error($core->LDAP->get_link()));
		}

		$entries = ldap_get_entries($core->LDAP->get_link(), $result);
		if($entries === FALSE)
		{
			throw new Exception('ldap_get_entries return error: '.ldap_error($core->LDAP->get_link()));
		}

		$i = $entries['count'];

		while($i > 0)
		{
			$i--;
			if(!empty($entries[$i]['samaccountname'][0]) && (!empty($entries[$i]['givenname'][0]) || !empty($entries[$i]['sn'][0])))
			{
				//print_r($entries[$i]);

				// *********************************************************

				$v_flags = 0;				
				$v_adid = bin2hex(@$entries[$i]['objectguid'][0]);  // unique active directory id
				$v_samaccountname = @$entries[$i]['samaccountname'][0];
				$v_first_name = @$entries[$i]['givenname'][0];
				$v_last_name = @$entries[$i]['sn'][0];
				$v_middle_name = @$entries[$i]['middlename'][0];
				$v_department = @$entries[$i]['department'][0];
				$v_organization = @$entries[$i]['company'][0];
				$v_position = @$entries[$i]['title'][0];
				$v_phone_internal = @$entries[$i]['telephonenumber'][0];
				$v_phone_external = ''; //@$entries[$i]['telephonenumber'][0];
				$v_phone_mobile = @$entries[$i]['mobile'][0];
				$v_mail = @$entries[$i]['mail'][0];
				$v_type = 0;
				$v_birthday = '0000-00-00';
				$v_reserved1 = '';
				$v_reserved2 = '';
				$v_reserved3 = '';
				$v_reserved4 = '';
				$v_reserved5 = '';

				$v_flags |= isset($entries[$i]['thumbnailphoto'][0]) ? PB_CONTACT_WITH_PHOTO : 0;
				$v_flags |= ((bool)(@$entries[$i]['useraccountcontrol'][0] & 0x2)) ? PB_CONTACT_AD_DISABLED : PB_CONTACT_VISIBLE;

				// *********************************************************

				if($core->db->select_ex($data, rpv("
						SELECT
							m.`id`,
							m.`adid`
						FROM
							`@contacts` AS m
						WHERE
							m.`adid` = !
						LIMIT 1
					",
					$v_adid
				)))
				{
					$v_id = $data[0][0];
					$core->db->put(rpv('
							UPDATE `@contacts` SET
								`samaccountname` = !,
								`last_name` = !,
								`first_name` = !,
								`middle_name` = !,
								`department` = !,
								`organization` = !,
								`position` = !,
								`phone_internal` = !,
								`phone_external` = !,
								`phone_mobile` = !,
								`mail` = !,
								`birthday` = !,
								`reserved1` = !,
								`reserved2` = !,
								`reserved3` = !,
								`reserved4` = !,
								`reserved5` = !,
								`type` = #,
								`flags` = ((`flags` & ~{%PB_CONTACT_AD_DELETED}) | #)
							WHERE
								`id` = #
							LIMIT 1
						',
						$v_samaccountname,
						$v_last_name,
						$v_first_name,
						$v_middle_name,
						$v_department,
						$v_organization,
						$v_position,
						$v_phone_internal,
						$v_phone_external,
						$v_phone_mobile,
						$v_mail,
						$v_birthday,
						$v_reserved1,
						$v_reserved2,
						$v_reserved3,
						$v_reserved4,
						$v_reserved5,
						$v_type,
						$v_flags & ~PB_CONTACT_VISIBLE,   // don't change visiblity for existing contact
						$v_id
					));

					$count_updated++;
				}
				else
				{
					$core->db->put(rpv('
							INSERT INTO `@contacts` (
								`adid`,
								`samaccountname`,
								`last_name`,
								`first_name`,
								`middle_name`,
								`department`,
								`organization`,
								`position`,
								`phone_internal`,
								`phone_external`,
								`phone_mobile`,
								`mail`,
								`birthday`,
								`reserved1`,
								`reserved2`,
								`reserved3`,
								`reserved4`,
								`reserved5`,
								`type`,
								`flags`
							) VALUES (!, !, !, !, !, !, !, !, !, !, !, !, !, !, !, !, !, !, #, #)
						',
						$v_adid,
						$v_samaccountname,
						$v_last_name,
						$v_first_name,
						$v_middle_name,
						$v_department,
						$v_organization,
						$v_position,
						$v_phone_internal,
						$v_phone_external,
						$v_phone_mobile,
						$v_mail,
						$v_birthday,
						$v_reserved1,
						$v_reserved2,
						$v_reserved3,
						$v_reserved4,
						$v_reserved5,
						$v_type,
						$v_flags
					));

					$v_id = $core->db->last_id();
					$count_added++;

					$data[] = array(
						$v_id,
						$v_adid,
						$v_samaccountname,
						$v_first_name,
						$v_last_name,
						$v_department,
						$v_organization,
						$v_position,
						$v_phone_internal,
						$v_phone_mobile,
						$v_mail,
						$v_birthday,
						$v_reserved1,
						$v_reserved2,
						$v_reserved3,
						$v_reserved4,
						$v_reserved5,
						$v_type,
						$v_flags
					);
				}
				//echo "\r\n".$db->get_last_error()."\r\n";

				if(isset($entries[$i]['thumbnailphoto'][0]))
				{
					$v_photo = @$entries[$i]['thumbnailphoto'][0];
					$w = 64;
					$h = 64;
					list($width, $height) = getimagesizefromstring($v_photo);
					$r = $w / $h;
					if($width/$height > $r)
					{
						$src_width = ceil($height*$r);
						$src_x = ceil(($width - $src_width)/2);
						$src_y = 0;
						$src_height = $height;
					}
					else
					{
						$src_height = ceil($width/$r);
						$src_y = ceil(($height - $src_height)/2);
						$src_x = 0;
						$src_width = $width;
					}
					$src = imagecreatefromstring($v_photo);
					$dst = imagecreatetruecolor($w, $h);
					imagecopyresampled($dst, $src, 0, 0, $src_x, $src_y, $w, $h, $src_width, $src_height);
					imagejpeg($dst, $upload_dir.DIRECTORY_SEPARATOR.'t'.$v_id.'.jpg', 100);
					imagedestroy($dst);
					imagedestroy($src);
				}
			}
		}

		if(isset($controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie']))
		{
			$cookie = $controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'];
		}
		else
		{
			$cookie = '';
		}

		ldap_free_result($result);
		//break;
	}
	while(!empty($cookie));

	//echo 'Updated: '.$count_updated.', added: '.$count_added.' contacts';
	//include(ROOTDIR.'templ'.DIRECTORY_SEPARATOR.'tpl.sync.php');
	echo '{"code": 0, "message": "'.json_escape('Added: '.$count_added.', Updated: '.$count_updated).'"}';
}
