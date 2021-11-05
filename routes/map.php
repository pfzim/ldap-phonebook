<?php

function map(&$core, $params, $post_data)
{
	$map_id = intval(@$params[1]);
	
	global $map_names;

	if($map_id < 1 || $map_id > PB_MAPS_COUNT)
	{
		$map_id = 1;
	}
	
	$core->db->select_assoc_ex($contacts, rpv('
			SELECT
				m.`id`,
				m.`first_name`,
				m.`last_name`,
				m.`middle_name`,
				m.`department`,
				m.`organization`,
				m.`position`,
				m.`phone_internal`,
				m.`phone_external`,
				m.`phone_mobile`,
				m.`mail`,
				m.`x`,
				m.`y`,
				m.`type`,
				m.`flags`
			FROM `@contacts` AS m
			WHERE
				(m.`flags` & {%PB_CONTACT_VISIBLE}) = {%PB_CONTACT_VISIBLE}
				AND m.`map` = #
		',
		$map_id
	));

	include(TEMPLATES_DIR.'tpl.map.php');
}

