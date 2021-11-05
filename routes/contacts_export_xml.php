<?php

function contacts_export_xml(&$core, $params, $post_data)
{
	assert_permission_ajax(0, PB_ACCESS_ADMIN);	// level 0 having Write access mean admin

	header('Content-Type: text/plain; charset=utf-8');
	header("Content-Disposition: attachment; filename=\"base.xml\"; filename*=utf-8''base.xml");

	$core->db->select_assoc_ex($result, rpv('SELECT * FROM `@contacts` AS m'));

	include(TEMPLATES_DIR.'tpl.contacts-export-xml.php');
}

