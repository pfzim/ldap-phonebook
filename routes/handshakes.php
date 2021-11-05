<?php

function handshakes(&$core, $params, $post_data)
{
	if(!$core->UserAuth->check_permission(0, PB_ACCESS_ADMIN))
	{
		$error_msg = LL('AccessDeniedToSection').' 0 '.LL('forUser').' '.$core->UserAuth->get_login().'!';
		include(TEMPLATES_DIR.'tpl.message.php');
		exit;
	}

	$handshakes = NULL;

	$total = 0;
	$offset = 0;
	if(!empty($params[1]))
	{
		$offset = intval($params[1]);
	}

	$search = '';
	$where = '';
	if(!empty($params[2]))
	{
		$search = trim(urldecode($params[2]));
		if(!empty($search))
		{
			$where = rpv('
					WHERE 
						h.`user` LIKE \'%{r0}%\'
						OR h.`computer` LIKE \'%{r0}%\'
						OR h.`ip` LIKE \'%{r0}%\'
				',
				sql_escape($search)
			);
		}
	}

	if($core->db->select_ex($handshakes_total, rpv('
			SELECT
				COUNT(*)
			FROM `@handshakes` AS h
			{r0}
		',
		$where
	)))
	{
		$total = intval($runbooks_total[0][0]);
	}

	$core->db->select_assoc_ex($handshakes, rpv('
			SELECT
				h.`id`,
				h.`date`,
				h.`user`,
				h.`computer`,
				h.`ip`
			FROM `@handshakes` AS h
			{r0}
			ORDER BY
				h.`date` DESC,
				h.`user`
			LIMIT {d1},100
		',
		$where,
		$offset
	));

	include(TEMPLATES_DIR.'tpl.handshakes.php');
}

