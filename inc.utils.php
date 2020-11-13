<?php

$dow_str = Array("Saturday", "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday");

function fseekline($fh, $line, $flag)
{
	$prev = "00";
	$pos = 0;
	$i = 0;

	$st = fstat($fh);
	$size = $st["size"];

	if($flag == 1)
	{
		$ln = 1;
		$pos = $size;

		while(!feof($fh))
		{
			$buf = fread($fh, 8192);
			$read = strlen($buf);
			for($i = 0; $i < $read; $i++)
			{
				$prev[1] = $prev[1];
				$prev[1] = $buf[$i];

				if(($prev[0] == "\r") && ($prev[1] == "\n"))
				{
					$ln++;
					if($ln >= $line)
					{
						return ftell($fp);
					}
				}
			}
		}
	}
	else if($flag == 2)
	{
		$pos = $size;

		while($pos > 0)
		{
			if($pos > 8192)
			{
				$pos -= 8192;
				$read = 8192;
			}
			else
			{
				$read = $pos;
				$pos = 0;
			}
			fseek($fh, $pos, SEEK_SET);

			$buf = fread($fh, $read);
			for($i = $read-1; $i >= 0; $i--)
			{
				$prev[1] = $prev[0];
				$prev[0] = $buf[$i];

				if(($prev[0] == "\r") && ($prev[1] == "\n"))
				{
					$line++;
					if($line == 0)
					{
						return $pos + $i + 2;
					}
				}
			}
		}
	}

	return 0;
}

function getpage($host, $uri, &$data)
{
	$header = "GET ".$uri." HTTP/1.0\r\n";
	$header .= "Host: ".$host."\r\n";
	$header .= "User-Agent: robot/0.1 (garant)\r\n";
	$header .= "Connection: close\r\n";
	$header .= "\r\n";

	$data = "";

	$handle = fsockopen($host, 80, $errno, $errstr, 30);
	if(!$handle)
	{
		return 1;
	}

	if(fwrite($handle, $header) === FALSE)
	{
		fclose($handle);
		return 2;
    }

    $buffer = "012345";

	while(!feof($handle) && (($buffer[0] !== "\r") || ($buffer[1] !== "\n") || ($buffer[2] !== "\r") || ($buffer[3] !== "\n")))
	{
		$buffer[0] = $buffer[1];
		$buffer[1] = $buffer[2];
		$buffer[2] = $buffer[3];
		$buffer[3] = fgetc($handle);
	}

	while(!feof($handle))
	{
		$data .= fread($handle, 8192);
	}

	fclose($handle);

	return 0;
}

function getline($data, &$pos)
{
	$prev = "00";
	$start = $pos;
	$end = strlen($data);

	if($end == ($start+1))
	{
		return FALSE;
	}

	while($pos < $end)
	{
		$prev[0] = $prev[1];
		$prev[1] = $data[$pos];
		$pos++;

		//if(($prev[0] == "\r") && ($prev[1] == "\n"))
		if($prev[1] == "\n")
		{
			//echo "substr($start, ".($pos-$start)." -2)\r\n";
			return substr($data, $start, $pos - $start -1);
		}
	}

	//echo "substr($start, ".($pos-$start)." -1)\r\n";
	return substr($data, $start, $pos - $start -1);
}

function splitdatebyweeks($fd, $fm, $fy, $td, $tm, $ty)
{
	$out = array();
	$week = 0;
	$out[$week] = array($fd, $fm, $fy, $td, $tm, $ty);

	while(($fd != $td) || ($fm != $tm) || ($fy != $ty))
	{
		//echo $fd.".".$fm.".".$fy."\r\n";
		if(dow($fd, $fm, $fy) == 1)
		{
			$out[$week][3] = $fd;
			$out[$week][4] = $fm;
			$out[$week][5] = $fy;
			$week++;
			incday($fd, $fm, $fy);
			$out[$week] = array($fd, $fm, $fy, $td, $tm, $ty);
		}
		else
		{
			incday($fd, $fm, $fy);
		}
	}
	return $out;
}

function dpm($m, $y)
{
	switch($m)
	{
		case 1:
		case 3:
		case 5:
		case 7:
		case 8:
		case 10:
		case 12:
			return 31;
		case 2:
			if((($y%4 == 0) && ($y%100 != 0)) || ($y%400 == 0)) return 29;
			return 28;
		case 4:
		case 6:
		case 9:
		case 11:
			return 30;
	}
	return 0;
}

function incday(&$d, &$m, &$y)
{
	$d++;
	if($d > dpm($m, $y))
	{
		$d = 1;
		$m++;
		if($m > 12)
		{
			$m = 1;
			$y++;
		}
		return TRUE;
	}
	return FALSE;
}

function getprevweek()
{
	$out = array();

	$today = getdate();

	$d = $today["mday"];
	$m = $today["mon"];
	$y = $today["year"];

	datesub($d, $m, $y, $today["wday"]);

	$out[] = array($d, $m, $y, 0, 0, 0);

	datesub($d, $m, $y, 7);

	$out[0][3] = $d;
	$out[0][4] = $m;
	$out[0][5] = $y;

	return $out;
}

function datecheck($d, $m, $y)
{
	if($y < 0)
	{
		return FALSE;
	}

	if(($m < 1) || ($m > 12))
	{
		return FALSE;
	}

	if(($d < 1) || ($d > dpm($m, $y)))
	{
		return FALSE;
	}

	return TRUE;
}

function datesub(&$d, &$m, &$y, $del)
{
	while($del >= $d)
	{
		$del -= $d;
		if($m == 1)
		{
			$m = 12;
			$y--;
		}
		else
		{
			$m--;
		}
		$d = dpm($m, $y);
	}

	if($del > 0)
	{
		$d -= $del;
	}
}

function dateadd(&$d, &$m, &$y, $del)
{
	while(($del+$d) > dpm($m, $y))
	{
		$del -= dpm($m, $y) - $d +1;
		$d = 1;
		$m++;
		if($m > 12)
		{
			$m = 1;
			$y++;
		}
	}

	if($del > 0)
	{
		$d += $del;
	}
}

function dow($d, $m, $y)
{
	$m2 = $m;

	if($m == 1)
	{
		$m2 = 13;
		$y = $y-1;
	}
	if($m == 2)
	{
		$m2 = 14;
		$y = $y-1;
	}

	$val4 = intval((($m2+1)*3)/5);
	$val5 = intval($y/4);
	$val6 = intval($y/100);
	$val7 = intval($y/400);
	$val8 = $d+($m2*2)+$val4+$y+$val5-$val6+$val7+2;
	$val9 = intval($val8/7);
	$val0 = $val8-($val9*7);

	return $val0;
}

function datecmp($td, $tm, $ty, $fd, $fm, $fy)
{
	if($ty < $fy)
	{
		return -1;
	}
	else if($ty > $fy)
	{
		return 1;
	}
	if($tm < $fm)
	{
		return -1;
	}
	else if($tm > $fm)
	{
		return 1;
	}
	if($td < $fd)
	{
		return -1;
	}
	else if($td > $fd)
	{
		return 1;
	}

	return 0;
}

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

/*
function dow0($d, $m, $y)
{
	$dt = getdate(mktime(0, 0, 0, $m, $d, $y));
	return $dt["wday"];
}
*/

function rpv_v1($string, $data)
{
	$i = 0;
	$n = 0;
	$len = strlen($string);
	$sc = 0;

	while($i < $len)
	{
		if($string[$i] === "?")
		{
			if(isset($data[$n]))
			{
				$string = substr_replace($string, "'".$data[$n]."'", $i, 1);
				$dl = strlen($data[$n])+2;
				$i += $dl;
				$len += $dl-1;
			}
			else if($string[$sc] === "{")
			{
				$ec = strpos($string, "}", $i);
				if($ec !== FALSE)
				{
					$i++;
					while($i < $ec)
					{
						if($string[$i] === "?")
						{
							$n++;
						}
						$i++;
					}
					$string = substr_replace($string, "", $sc, $ec-$sc+1);
					$len -= $ec-$sc+1;
					$i = $sc;
					$sc = 0;
				}
				else
				{
					return "";
				}
			}
			else
			{
				return "";
			}
			$n++;
		}
		else if($string[$i] === "{")
		{
			$sc = $i;
			$i++;
		}
		else if($string[$i] === "}")
		{
			if($string[$sc] === "{")
			{
				$string = substr_replace($string, "", $i, 1);
				$string = substr_replace($string, "", $sc, 1);
				$len -= 2;
				$sc = 0;
				$i -= 2;
			}
			else
			{
				return "";
			}
		}
		else
		{
			$i++;
		}
	}

	return $string;
}

function rpv_v2($string, $data)
{
	$i = 0;
	$n = 0;
	$len = strlen($string);
	$sc = 0;

	while($i < $len)
	{
		if($string[$i] === "?")			// unsafe injection
		{
			$val = isset($data[$n])?$data[$n]:"";
			$string = substr_replace($string, $val, $i, 1);
			$dl = strlen($val);
			$i += $dl;
			$len += $dl-1;
			$n++;
		}
		else if($string[$i] === "!")	// safe string
		{
			$val = "'".(isset($data[$n])?(get_magic_quotes_gpc()?$data[$n]:addslashes(trim($data[$n]))):"")."'";
			$string = substr_replace($string, $val, $i, 1);
			$dl = strlen($val);
			$i += $dl;
			$len += $dl-1;
			$n++;
		}
		else if($string[$i] === "#")	// safe integer
		{
			$val = (isset($data[$n])?intval($data[$n]):"0");
			$string = substr_replace($string, $val, $i, 1);
			$dl = strlen($val);
			$i += $dl;
			$len += $dl-1;
			$n++;
		}
		else if($string[$i] === "~")	// safe float
		{
			$val = (isset($data[$n])?floatval($data[$n]):"0");
			$string = substr_replace($string, $val, $i, 1);
			$dl = strlen($val);
			$i += $dl;
			$len += $dl-1;
			$n++;
		}
		else if($string[$i] === "@")
		{
			$val = defined("RPV_PREFIX")?RPV_PREFIX:"";
			$string = substr_replace($string, $val, $i, 1);
			$dl = strlen($val);
			$i += $dl;
			$len += $dl-1;
		}
		else if($string[$i] === "{")
		{
			$sc = $i;
			if(!isset($data[$n]) || !$data[$n])
			{
				$ec = strpos($string, "}", $i);
				if($ec !== FALSE)
				{
					$i++;
					while($i < $ec)
					{
						if(($string[$i] === "!") || ($string[$i] === "?") || ($string[$i] === "#"))
						{
							$n++;
						}
						$i++;
					}
					$string = substr_replace($string, "", $sc, $ec-$sc+1);
					$len -= $ec-$sc+1;
					$i = $sc;
					$sc = 0;
				}
				else
				{
					return "";
				}
			}
			else
			{
				$sc = $i;
				$i++;
			}

			$n++;
		}
		else if($string[$i] === "}")
		{
			if($string[$sc] === "{")
			{
				$string = substr_replace($string, "", $i, 1);
				$string = substr_replace($string, "", $sc, 1);
				$len -= 2;
				$sc = 0;
				$i -= 2;
			}
			else
			{
				return "";
			}
		}
		else
		{
			$i++;
		}
	}

	return $string;
}

function build_query($qa)
{
	$out = "";
	foreach($qa as $name => $val)
	{
		$out .= empty($out)?"?":"&".urlencode($name)."=".urlencode($val);
	}

	return $out;
}


/**
 *  \brief Echo HTML escaped string
 *  
 *  \param [in] $str String for output
 *  \return None
 */

function eh($str)
{
	echo htmlspecialchars($str);
}

function formatBytes($bytes, $precision = 3) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    // Uncomment one of the following alternatives
    $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 * $pow));

    return round($bytes, $precision) . ' ' . $units[$pow];
}

function json_escape($value) //json_escape
{
    $escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
    $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
    $result = str_replace($escapers, $replacements, $value);
    return $result;
}

function sql_escape($value)
{
    $escapers = array("\\", "\"", "\n", "\r", "\t", "\x08", "\x0c", "'", "\x1A", "\x00"); // "%", "_"
    $replacements = array("\\\\", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b", "\\'", "\\Z", "\\0");
    $result = str_replace($escapers, $replacements, $value);
    return $result;
}

function rpv_old()
{
	$data = func_get_args();
	$string = $data[0];
	$i = 0;
	$n = 1;
	$len = strlen($string);
	$sc = 0;

	while($i < $len)
	{
		if($string[$i] === "?")			// unsafe injection
		{
			$val = isset($data[$n])?$data[$n]:"";
			$string = substr_replace($string, $val, $i, 1);
			$dl = strlen($val);
			$i += $dl;
			$len += $dl-1;
			$n++;
		}
		else if($string[$i] === "!")	// safe string
		{
			$val = "'".(isset($data[$n])?sql_escape(trim($data[$n])):"")."'";
			$string = substr_replace($string, $val, $i, 1);
			$dl = strlen($val);
			$i += $dl;
			$len += $dl-1;
			$n++;
		}
		else if($string[$i] === "#")	// safe integer
		{
			$val = (isset($data[$n])?intval($data[$n]):"0");
			$string = substr_replace($string, $val, $i, 1);
			$dl = strlen($val);
			$i += $dl;
			$len += $dl-1;
			$n++;
		}
		/*
		else if($string[$i] === "~")	// safe float - conflict with binary NOT operator
		{
			$val = (isset($data[$n])?floatval($data[$n]):"0");
			$string = substr_replace($string, $val, $i, 1);
			$dl = strlen($val);
			$i += $dl;
			$len += $dl-1;
			$n++;
		}
		*/
		else if($string[$i] === "@")
		{
			$val = defined("DB_PREFIX")?DB_PREFIX:"";
			$string = substr_replace($string, $val, $i, 1);
			$dl = strlen($val);
			$i += $dl;
			$len += $dl-1;
		}
		else
		{
			$i++;
		}
	}

	return $string;
}

/**
 *  \brief Replace placeholders with numbered parameters (zero-based)
 *  
 *  \return Return replaced string
 *  
 *  \details {d0} - safe integer
 *           {s0} - safe trimmed sql string
 *           {f0} - safe float
 *           {r0} - unsafe raw string
 *           @    - DB_PREFIX
 *           {{   - {
 *           {@   - @
 *           {#   - #
 *           {!   - !
 *           #    - safe integer (param by order)
 *           !    - safe trimmed sql string (param by order)
 */
 
function rpv()
{
	// Example: 'SELECT * WHERE int = {d0} AND string = {s1} AND float = {f2} AND raw IN {r3} AND escape = "{{}"',  123, 'string'

	$out_string = '';

	$data = func_get_args();

	$string = $data[0];
	$len = strlen($string);

	$i = 0;
	$n = 1;

	while($i < $len)
	{
		if($string[$i] === '@')
		{
			$out_string .= defined("DB_PREFIX") ? DB_PREFIX : '';
		}
		else if($string[$i] === '#')
		{
			$out_string .= intval($data[$n]);
			$n++;
		}
		else if($string[$i] === '!')
		{
			$out_string .= '\''.sql_escape(trim($data[$n])).'\'';
			$n++;
		}
		else if($string[$i] === '{')
		{
			$i++;
			if($string[$i] === '{')
			{
				$out_string .= '{';
			}
			else if($string[$i] === '@')
			{
				$out_string .= '@';
			}
			else if($string[$i] === '#')
			{
				$out_string .= '#';
			}
			else if($string[$i] === '!')
			{
				$out_string .= '!';
			}
			else
			{
				$prefix = $string[$i];
				$param = '';
				$i++;
				while($string[$i] !== '}')
				{
					$param .= $string[$i];
					$i++;
				}
				
				switch($prefix)
				{
					case 'd':
						$out_string .= intval($data[intval($param) + 1]);
						break;
					case 's':
						$out_string .= '\''.sql_escape(trim($data[intval($param) + 1])).'\'';
						break;
					case 'f':
						$out_string .= floatval($data[intval($param) + 1]);
						break;
					case 'r':
						$out_string .= $data[intval($param) + 1];
						break;
				}
			}
		}
		else
		{
			$out_string .= $string[$i];
		}
		
		$i++;
	}

	return $out_string;
}

