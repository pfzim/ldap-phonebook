<?php

function memcached_flush(&$core, $params, $post_data)
{
	assert_permission_ajax(0, RB_ACCESS_EXECUTE);	// non-priveleged users cannot flush memcached

	if(defined('USE_MEMCACHED') && USE_MEMCACHED)
	{
		$core->Mem->flush();
		echo '{"code": 0, "message": "Cache cleared"}';
	}
	else
	{
		echo '{"code": 0, "message": "memcached is not configured"}';
	}
}
