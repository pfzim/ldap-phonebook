<?php

function tools(&$core, $params, $post_data)
{
	$core->db->select_assoc_ex($config, rpv('SELECT m.`uid`, m.`name`, m.`value`, m.`description` FROM @config AS m WHERE m.`uid` = 0 OR m.`uid` = # ORDER BY m.`uid`, m.`name`', $core->UserAuth->get_id()));

	include(TEMPLATES_DIR.'tpl.tools.php');
}
