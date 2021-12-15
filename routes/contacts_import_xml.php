<?php

function contacts_import_xml(&$core, $params, $post_data)
{
	assert_permission_ajax(0, PB_ACCESS_ADMIN);	// level 0 having Write access mean admin

	if(!file_exists(@$_FILES['file']['tmp_name']))
	{
		echo '{"code": 1, "message": "Invalid XML"}';
		return;
	}

	$result_ok = 0;
	$result_fail = 0;

	$xml = simplexml_load_file(@$_FILES['file']['tmp_name']);
	if($xml === FALSE)
	{
		echo '{"code": 1, "message": "XML load and parse failed"}';
		return;
	}

	if(!$core->db->put(rpv('TRUNCATE TABLE @contacts')))
	{
		echo '{"code": 1, "message": "Truncate table failed"}';
		return;
	}

	foreach($xml->children() as $contact)
	{
		$keys = '';
		$values = '';
		foreach($contact->children() as $key => $value)
		{
			if($key != 'id')
			{
				if(!empty($keys))
				{
					$keys .= ', ';
					$values .= ', ';
				}
				$keys .= '`'.sql_escape($key).'`';
				$values .= (($key == 'birthday') && empty($value))?'NULL':'\''.sql_escape($value).'\'';
			}
		}

		if($core->db->put(rpv('INSERT INTO @contacts ({r0}) VALUES ({r1})', $keys, $values)))
		{
			$result_ok++;
		}
		else
		{
			$result_fail++;
		}
	}

	echo '{"code": 0, "ok": '.$result_ok.', "fail": '.$result_fail.', "message": "XML imported (OK: '.$result_ok.', FAIL: '.$result_fail.')"}';
}

