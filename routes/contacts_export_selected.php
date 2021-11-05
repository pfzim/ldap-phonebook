<?php

function contacts_export_selected(&$core, $params, $post_data)
{
	//assert_permission_ajax(0, PB_ACCESS_ADMIN);	// level 0 having Write access mean admin

	header('Content-Type: text/plain; charset=utf-8');
	header("Content-Disposition: attachment; filename=\"base.xml\"; filename*=utf-8''base.xml");

	$result = array();

	if(isset($post_data['list']))
	{
		$j = 0;
		$list_safe = '';
		$list = explode(',', $post_data['list']);
		foreach($list as &$id)
		{
			if($j > 0)
			{
				$list_safe .= ',';
			}

			$list_safe .= intval($id);
			$j++;
		}

		if($j > 0)
		{
			$core->db->select_ex($result, rpv('SELECT m.`id`, m.`samname`, m.`fname`, m.`lname`, m.`dep`, m.`org`, m.`pos`, m.`pint`, m.`pcell`, m.`mail` FROM `@contacts` AS m WHERE m.`id` IN ({r0}) ORDER BY m.`lname`, m.`fname`', $list_safe));
		}
	}

	include(TEMPLATES_DIR.'tpl.contacts-export.php');
}

