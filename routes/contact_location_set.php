<?php

function contact_location_set(&$core, $params, $post_data)
{
	global $g_maps_count;

	$id = intval(@$post_data['id']);
	$map_id = intval(@$post_data['map']);
	$pos_x = intval(@$post_data['x']);
	$pos_y = intval(@$post_data['y']);

	assert_permission_ajax(0, PB_ACCESS_ADMIN);	// level 0 having Write access mean admin
	
	if(($map_id < 0) || ($map_id >= $g_maps_count))
	{
		echo '{"code": 1, "message": "Invalid map identifier"}';
		exit;
	}

	$core->db->put(rpv('UPDATE `@contacts` SET `map` = #, `x` = #, `y` = # WHERE `id` = # LIMIT 1', $map_id, $pos_x, $pos_y, $id));

	echo '{"code": 0, "id": '.$id.', "map": '.json_escape($map_id).', "x": '.json_escape($pos_x).', "y": '.json_escape($pos_y).', "message": "Location set (ID '.$id.')"}';
}

