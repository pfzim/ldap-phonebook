<?php

function contact_get(&$core, $params, $post_data)
{
	$contact_id = intval(@$params[1]);

	assert_permission_ajax(0, PB_ACCESS_ADMIN);	// level 0 having Write access mean admin

	if(!$contact_id)
	{
		echo '{"code": 1, "message": "Undefined contact id"}';
	}

	if(!$core->db->select_assoc_ex($contact, rpv('
		SELECT *
		FROM `@contacts` AS m
		WHERE m.`id` = #
		LIMIT 1', $contact_id
	)))
	{
		echo '{"code": 1, "message": "Contact not found"}';
	}

	$contact = &$contact[0];
	$computers = array('', '', '');

	if($core->db->select_ex($comps, rpv('SELECT m.`computer` FROM `@handshakes` AS m WHERE m.`user` = ! ORDER BY m.`date` DESC LIMIT 3', $contact['samaccountname'])))
	{
		$i = 0;
		foreach($comps as &$comp)
		{
			$computers[$i++] = &$comp[0];
		}
	}

	$result_json = array(
		'code' => 0,
		'message' => '',
		'data' => &$contact,
		'computers' => &$computers
	);

	echo json_encode($result_json);


	/* // *** start cut here ***
	$line = '';

	$f = @fopen('\\\\server.with.logs\\logs$\\'.$db->data[0][1].'.txt', 'r');
	if($f !== false)
	{
		$cursor = -1;

		fseek($f, $cursor, SEEK_END);
		$char = fgetc($f);

		for($i = 0; $i < 3; $i++)
		{
			// Trim trailing newline chars of the file
			while ($char === "\n" || $char === "\r") {
				fseek($f, $cursor--, SEEK_END);
				$char = fgetc($f);
			}

			// Read until the start of file or first newline char
			while ($char !== false && $char !== "\n" && $char !== "\r") {
				// Prepend the new char
				$line = $char . $line;
				fseek($f, $cursor--, SEEK_END);
				$char = fgetc($f);
			}

			if(preg_match('/logged into ([^\s]+) ip \d+\.\d+\.\d+\.\d+ using/', $line, $match))
			{
				$compname[$i] = $match[1];
			}
		}
	}
	// *** end cut here *** */
}

