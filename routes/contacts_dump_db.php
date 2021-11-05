<?php

function contacts_dump_db(&$core, $params, $post_data)
{
	assert_permission_ajax(0, PB_ACCESS_ADMIN);	// level 0 having Write access mean admin

	header('Content-Type: text/plain; charset=utf-8');
	header("Content-Disposition: attachment; filename=\"base.sql\"; filename*=utf-8''base.sql");

	echo rpv('TRUNCATE TABLE @contacts;')."\r\n";

	$core->db->select_assoc_ex($result, rpv('SELECT * FROM `@contacts` AS m'));

	foreach($result as &$row)
	{
		$keys = '';
		$values = '';
		foreach($row as $key => $value)
		{
			if($key != 'id')
			{
				if(!empty($keys))
				{
					$keys .= ', ';
					$values .= ', ';
				}
				$keys .= '`'.sql_escape($key).'`';
				$values .= '\''.sql_escape($value).'\'';
			}
		}

		echo rpv('INSERT INTO @contacts ({r0}) VALUES ({r1});', $keys, $values)."\r\n";
	}
}

