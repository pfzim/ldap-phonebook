<?php

function contact_save(&$core, $params, $post_data)
{
	assert_permission_ajax(0, PB_ACCESS_ADMIN);	// level 0 having Write access mean admin

	$result_json = array(
		'code' => 0,
		'message' => '',
		'errors' => array()
	);

	$v_id 				= intval(@$post_data['id']);
	$v_last_name 		= trim(@$post_data['last_name']);
	$v_first_name 		= trim(@$post_data['first_name']);
	$v_middle_name 		= trim(@$post_data['middle_name']);
	$v_department 		= trim(@$post_data['department']);
	$v_organization 	= trim(@$post_data['organization']);
	$v_position 		= trim(@$post_data['position']);
	$v_phone_internal 	= trim(@$post_data['phone_internal']);
	$v_phone_external	= trim(@$post_data['phone_external']);
	$v_phone_mobile 	= trim(@$post_data['phone_mobile']);
	$v_mail 			= trim(@$post_data['mail']);
	$v_birthday 		= trim(@$post_data['birthday']);
	$v_type 			= trim(@$post_data['type']);
	$v_reserved1 		= ''; // trim(@$post_data['reserved1']);
	$v_reserved2 		= ''; // trim(@$post_data['reserved2']);
	$v_reserved3 		= ''; // trim(@$post_data['reserved3']);
	$v_reserved4 		= ''; // trim(@$post_data['reserved4']);
	$v_reserved5 		= ''; // trim(@$post_data['reserved5']);

	if(empty($v_last_name))
	{
		$result_json['code'] = 1;
		$result_json['errors'][] = array('name' => 'last_name', 'msg' => LL('ThisFieldRequired'));
	}
	
	if(!empty($v_birthday))
	{
		$d = explode('.', $v_birthday, 3);
		$nd = intval(@$d[0]);
		$nm = intval(@$d[1]);
		$ny = intval(@$d[2]);
		$v_birthday = sprintf('\'%04d-%02d-%02d\'', $ny, $nm, $nd);
		$v_bday_human = sprintf('%02d.%02d.%04d', $nd, $nm, $ny);

		if(!datecheck($nd, $nm, $ny))
		{
			$result_json['code'] = 1;
			$result_json['errors'][] = array('name' => 'birthday', 'msg' => LL('IncorrectDate').' DD.MM.YYYY');
		}
	}
	else
	{
		$v_birthday = 'NULL';
	}

	if($result_json['code'])
	{
		$result_json['message'] = LL('NotAllFilled');
		echo json_encode($result_json);
		exit;
	}

	if(!$v_id)
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
				) VALUES (\'\', \'\', {s0}, {s1}, {s2}, {s3}, {s4}, {s5}, {s6}, {s7}, {s8}, {s9}, {r10}, {s11}, {s12}, {s13}, {s14}, {s15}, {d16}, {d17})
			',
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
			PB_CONTACT_VISIBLE
		));

		$v_id = $core->db->last_id();

		echo '{"code": 0, "id": '.$v_id.', "message": "Added (ID '.$v_id.')"}';
	}
	else
	{
		$core->db->put(rpv('
				UPDATE `@contacts` SET
					`samaccountname` = \'\',
					`last_name` = {s0},
					`first_name` = {s1},
					`middle_name` = {s2},
					`department` = {s3},
					`organization` = {s4},
					`position` = {s5},
					`phone_internal` = {s6},
					`phone_external` = {s7},
					`phone_mobile` = {s8},
					`mail` = {s9},
					`birthday` = {r10},
					`reserved1` = {s11},
					`reserved2` = {s12},
					`reserved3` = {s13},
					`reserved4` = {s14},
					`reserved5` = {s15},
					`type` = {d16}
				WHERE
					`id` = {d17}
					AND `adid` = \'\'
				LIMIT 1
			',
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
			$v_id
		));

		echo '{"code": 0, "id": '.$v_id.',"message": "Updated (ID '.$v_id.')"}';
	}
}

