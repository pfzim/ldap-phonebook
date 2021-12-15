<?php

function contact_photo_set(&$core, $params, $post_data)
{
	$id = intval(@$post_data['id']);

	assert_permission_ajax(0, PB_ACCESS_ADMIN);	// level 0 having Write access mean admin

	if(!$id)
	{
		echo '{"code": 1, "message": "Invalid identifier"}';
		exit;
	}

	if(!file_exists(@$_FILES['file']['tmp_name']))
	{
		echo '{"code": 1, "message": "Invalid photo"}';
		exit;
	}

	$s_photo = file_get_contents(@$_FILES['file']['tmp_name']);
	$w = 64;
	$h = 64;
	list($width, $height) = getimagesizefromstring($s_photo);
	$r = $w / $h;
	if($width/$height > $r)
	{
		$src_width = ceil($height*$r);
		$src_x = ceil(($width - $src_width)/2);
		$src_y = 0;
		$src_height = $height;
	}
	else
	{
		$src_height = ceil($width/$r);
		$src_y = ceil(($height - $src_height)/2);
		$src_x = 0;
		$src_width = $width;
	}
	$src = imagecreatefromstring($s_photo);
	$dst = imagecreatetruecolor($w, $h);
	imagecopyresampled($dst, $src, 0, 0, $src_x, $src_y, $w, $h, $src_width, $src_height);
	imagejpeg($dst, ROOT_DIR.'photos'.DIRECTORY_SEPARATOR.'t'.$id.'.jpg', 100);
	imagedestroy($dst);
	imagedestroy($src);

	$core->db->put(rpv('UPDATE `@contacts` SET `flags` = (`flags` | {%PB_CONTACT_WITH_PHOTO}) WHERE `id` = # LIMIT 1', $id));

	echo '{"code": 0, "id": '.$id.', "message": "Photo set (ID '.$id.')"}';
}

