<?php

function contacts(&$core, $params, $post_data)
{
	global $map_names;

	$action = @$params[0];
	$offset = 0;
	$sort = intval($core->Config->get_user('contacts_sort', @$_SESSION['contacts_sort']));
	$search = '';
	$need_json = 0;
	
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

			case 'json':
				{
					$i++;
					if($i < $params_count)
					{
						$need_json = intval($params[$i]);
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
		
		$search_phone = preg_replace('/[^0-9]/', '', $search);
		
		if(!empty($search_phone))
		{
			$search_phone = rpv('
					OR REGEXP_REPLACE(c.`phone_internal`, \'[^0-9]\', \'\') LIKE \'%{r0}%\'
					OR REGEXP_REPLACE(c.`phone_external`, \'[^0-9]\', \'\') LIKE \'%{r0}%\'
					OR REGEXP_REPLACE(c.`phone_mobile`, \'[^0-9]\', \'\') LIKE \'%{r0}%\'
				',
				sql_escape($search_phone)
			);
		}

		$where .= rpv('
				(
					c.`last_name` LIKE \'%{r0}%\'
					OR c.`first_name` LIKE \'%{r0}%\'
					OR c.`middle_name` LIKE \'%{r0}%\'
					OR c.`mail` LIKE \'%{r0}%\'
					OR c.`position` LIKE \'%{r0}%\'
					OR c.`department` LIKE \'%{r0}%\'
					{r1}
				)
			',
			sql_escape($search),
			$search_phone
		);
	}
	
	$sort_direction = '';
	if($sort & 0x0100)
	{
		$sort_direction = ' DESC';
	}
	
	switch($sort & 0x00FF)
	{
		case 1: 
			$order = 'c.`phone_internal`'.$sort_direction.', c.`last_name`'.$sort_direction.', c.`first_name`'.$sort_direction.', c.`middle_name`'.$sort_direction.', c.`id`'.$sort_direction;
			break;
		case 2: 
			$order = 'c.`phone_external`'.$sort_direction.', c.`last_name`'.$sort_direction.', c.`first_name`'.$sort_direction.', c.`middle_name`'.$sort_direction.', c.`id`'.$sort_direction;
			break;
		case 3: 
			$order = 'c.`phone_mobile`'.$sort_direction.', c.`last_name`'.$sort_direction.', c.`first_name`'.$sort_direction.', c.`middle_name`'.$sort_direction.', c.`id`'.$sort_direction;
			break;
		case 4: 
			$order = 'c.`mail`'.$sort_direction.', c.`last_name`'.$sort_direction.', c.`first_name`'.$sort_direction.', c.`middle_name`'.$sort_direction.', c.`id`'.$sort_direction;
			break;
		case 5: 
			$order = 'c.`position`'.$sort_direction.', c.`last_name`'.$sort_direction.', c.`first_name`'.$sort_direction.', c.`middle_name`'.$sort_direction.', c.`id`'.$sort_direction;
			break;
		case 6: 
			$order = 'c.`department`'.$sort_direction.', c.`last_name`'.$sort_direction.', c.`first_name`'.$sort_direction.', c.`middle_name`'.$sort_direction.', c.`id`'.$sort_direction;
			break;
		default: 
			//$sort = 0;
			$order = 'c.`last_name`'.$sort_direction.', c.`first_name`'.$sort_direction.', c.`middle_name`'.$sort_direction.', c.`id`'.$sort_direction;
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
			AND c.`birthday` IS NOT NULL
			AND DATE_ADD(
				c.`birthday`,
				INTERVAL (YEAR(CURDATE()) - YEAR(c.`birthday`) + IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(c.`birthday`), 1, 0)) YEAR
			) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
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

	if($need_json || ($action === 'contacts_search'))
	{
		$result_json = array(
			'code' => 0,
			'message' => '',
			'offset' => $offset,
			'total' => $total,
			'data' => &$phones
		);

		echo json_encode($result_json);
	}
	else
	{
		include(TEMPLATES_DIR.'tpl.contacts.php');
	}
}
