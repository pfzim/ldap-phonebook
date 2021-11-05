<?php

function contacts(&$core, $params, $post_data)
{
	$action = @$params[0];

	$is_admin = $core->UserAuth->check_permission(0, PB_ACCESS_ADMIN);

	if($is_admin && ($action == 'all'))
	{
		$where = '';
	}
	else
	{
		$where = rpv('WHERE ((c.`flags` & {%PB_CONTACT_VISIBLE}) = {%PB_CONTACT_VISIBLE})');
	}

	$total = 0;
	$offset = 0;
	if(!empty($params[1]))
	{
		$offset = intval($params[1]);
	}

	$search = '';
	if(!empty($params[2]))
	{
		$search = trim(urldecode($params[2]));
		if(!empty($search))
		{
			if(empty($where))
			{
				$where = 'WHERE ';
			}
			else
			{
				$where .= ' AND ';
			}

			$where .= rpv('
					(
						c.`last_name` LIKE \'%{r0}%\'
						OR c.`first_name` LIKE \'%{r0}%\'
						OR c.`middle_name` LIKE \'%{r0}%\'
						OR c.`phone_internal` LIKE \'%{r0}%\'
						OR c.`phone_external` LIKE \'%{r0}%\'
						OR c.`phone_mobile` LIKE \'%{r0}%\'
						OR c.`mail` LIKE \'%{r0}%\'
						OR c.`position` LIKE \'%{r0}%\'
						OR c.`department` LIKE \'%{r0}%\'
					)
				',
				sql_escape($search)
			);
		}
	}

	$core->db->select_assoc_ex($birthdays, rpv("
		SELECT
			c.`id`,
			c.`adid`,
			c.`last_name`,
			c.`first_name`,
			c.`middle_name`,
			DATE_FORMAT(c.`birthday`, '%d.%m') AS DayMonth
		FROM
			`@contacts` AS c
		WHERE
			(c.`flags` & {%PB_CONTACT_VISIBLE}) = {%PB_CONTACT_VISIBLE}
			AND MONTH(c.`birthday`) = MONTH(NOW())
			AND DAY(c.`birthday`) >= DAY(NOW())
			AND DAY(c.`birthday`) <= DAY(NOW() + INTERVAL 7 DAY)
		ORDER BY
			MONTH(c.`birthday`),
			DAY(c.`birthday`),
			c.`last_name`,
			c.`first_name`,
			c.`middle_name`
	"));

	if($core->db->select_ex($handshakes_total, rpv('
			SELECT
				COUNT(*)
			FROM `@contacts` AS c
			{r0}
		',
		$where
	)))
	{
		$total = intval($runbooks_total[0][0]);
	}

	$core->db->select_assoc_ex($phones, rpv('
			SELECT
				*
			FROM
				`@contacts` AS c
				{r0}
			ORDER BY
				c.`last_name`,
				c.`first_name`,
				c.`middle_name`
			LIMIT {d1},100
		',
		$where,
		$offset
	));

	include(TEMPLATES_DIR.'tpl.contacts.php');
}
