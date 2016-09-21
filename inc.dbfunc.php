<?php
require_once("inc.db.conf.php");

$link = NULL;

function db_connect()
{
	global $link;
	global $error_msg;
	
	$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWD, DB_NAME);
	if($link)
	{
		if(!mysqli_set_charset($link, DB_CPAGE))
		{
			$error_msg = mysqli_error($link);
			mysqli_close($link);
			$link = NULL;
			return NULL;
		}
	}
	
	return $link;
}

function db_select($query)
{
	global $link;
	global $error_msg;
	
	if(!$link)
	{
		return FALSE;
	}

	$res = mysqli_query($link, $query);
	if(!$res)
	{
		$error_msg = mysqli_error($link);
		return FALSE;
	}
	
	if(mysqli_num_rows($res) <= 0)
	{
		return FALSE;		
	}

	$out = array();
	
	while($row = mysqli_fetch_row($res))
	{
		$out[] = $row;
	}
	
	mysqli_free_result($res);
	
	return $out;
}

function db_put($query)
{
	global $link;
	global $error_msg;
	
	if(!$link)
	{
		return FALSE;
	}

	$res = mysqli_query($link, $query);
	if(!$res)
	{
		$error_msg = mysqli_error($link);
		return FALSE;
	}
	
	return mysqli_affected_rows($link);
}

function db_last_id()
{
	global $link;
	
	return mysqli_insert_id($link);
}

function db_disconnect()
{
	global $link;
	
	if($link)
	{
		mysqli_close($link);
		$link = NULL;
	}
}
