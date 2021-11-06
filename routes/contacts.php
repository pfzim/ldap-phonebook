<?php

function contacts(&$core, $params, $post_data)
{
	$action = @$params[0];
	$offset = 0;
	$sort = $core->Config->get_user('contacts_sort', @$_SESSION['contacts_sort']);
	$search = '';
	
	$i = 1;
	$params_count = count($params);
	while($i < $params_count)
	{
		switch($params[$i])
		{
			case 'sort':
				{
					$i++;
					if($i < $params_count)
					{
						$sort = intval($params[$i]);
						$_SESSION['contacts_sort'] = $sort;
						$core->Config->set_user('contacts_sort', $sort);
					}
				}
				break;

			case 'search':
				{
					$i++;
					if($i < $params_count)
					{
						$search = trim(urldecode($params[$i]));
					}
				}
				break;

			case 'offset':
				{
					$i++;
					if($i < $params_count)
					{
						$offset = intval($params[$i]);
					}
				}
				break;
		}

		$i++;
	}

	$is_admin = $core->UserAuth->check_permission(0, PB_ACCESS_ADMIN);

	if($is_admin && ($action == 'all'))
	{
		$where = '';
	}
	else
	{
		$where = rpv('WHERE ((c.`flags` & {%PB_CONTACT_VISIBLE}) = {%PB_CONTACT_VISIBLE})');
	}

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
	
	switch($sort)
	{
		case 1: 
			$order = 'c.`phone_internal`, c.`last_name`, c.`first_name`, c.`middle_name`, c.`id`';
			break;
		case 2: 
			$order = 'c.`phone_external`, c.`last_name`, c.`first_name`, c.`middle_name`, c.`id`';
			break;
		case 3: 
			$order = 'c.`phone_mobile`, c.`last_name`, c.`first_name`, c.`middle_name`, c.`id`';
			break;
		case 4: 
			$order = 'c.`mail`, c.`last_name`, c.`first_name`, c.`middle_name`, c.`id`';
			break;
		case 5: 
			$order = 'c.`position`, c.`last_name`, c.`first_name`, c.`middle_name`, c.`id`';
			break;
		case 6: 
			$order = 'c.`department`, c.`last_name`, c.`first_name`, c.`middle_name`, c.`id`';
			break;
		default: 
			$sort = 0;
			$order = 'c.`last_name`, c.`first_name`, c.`middle_name`, c.`id`';
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

	$total = 0;

	if($core->db->select_ex($phones_total, rpv('
			SELECT
				COUNT(*)
			FROM `@contacts` AS c
			{r0}
		',
		$where
	)))
	{
		$total = intval($phones_total[0][0]);
	}

	$core->db->select_assoc_ex($phones, rpv('
			SELECT
				*
			FROM
				`@contacts` AS c
				{r0}
			ORDER BY
				{r1}
			LIMIT {d2},100
		',
		$where,
		$order,
		$offset
	));

	include(TEMPLATES_DIR.'tpl.contacts.php');
}
