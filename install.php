<?php
/*
    One file installer
    Copyright (C) 2016-2020 Dmitry V. Zimin

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

error_reporting(0);

if (!defined('ROOT_DIR'))
{
	define('ROOT_DIR', dirname(__FILE__).DIRECTORY_SEPARATOR);
}

if(file_exists(ROOT_DIR.'inc.config.php'))
{
	header('Content-Type: text/plain; charset=utf-8');
	echo 'Configuration file already exist. Remove inc.config.php before running installation';
	exit;
}

$modules = array(
	'ldap',
	'SimpleXML',
	'memcached',
	'json',
	'curl',
	'pcre',
	'gd',
	'mysqli'
);

$sql = array(
<<<'EOT'
CREATE TABLE  `#DB_NAME#`.`pb_contacts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `adid` varchar(32) NOT NULL DEFAULT '',
  `samaccountname` varchar(20) NOT NULL DEFAULT '',
  `first_name` varchar(255) NOT NULL DEFAULT '',
  `last_name` varchar(255) NOT NULL DEFAULT '',
  `middle_name` varchar(255) NOT NULL DEFAULT '',
  `department` varchar(255) NOT NULL DEFAULT '',
  `organization` varchar(255) NOT NULL DEFAULT '',
  `position` varchar(255) NOT NULL DEFAULT '',
  `phone_internal` varchar(255) NOT NULL DEFAULT '',
  `phone_external` varchar(255) NOT NULL DEFAULT '',
  `phone_mobile` varchar(255) NOT NULL DEFAULT '',
  `mail` varchar(255) NOT NULL DEFAULT '',
  `birthday` date DEFAULT NULL,
  `reserved1` varchar(255) NOT NULL DEFAULT '',
  `reserved2` varchar(255) NOT NULL DEFAULT '',
  `reserved3` varchar(255) NOT NULL DEFAULT '',
  `reserved4` varchar(255) NOT NULL DEFAULT '',
  `reserved5` varchar(255) NOT NULL DEFAULT '',
  `type` int(10) unsigned NOT NULL DEFAULT 0,
  `map` int(10) unsigned NOT NULL DEFAULT 0,
  `x` int(10) unsigned NOT NULL DEFAULT 0,
  `y` int(10) unsigned NOT NULL DEFAULT 0,
  `flags` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOT
,
<<<'EOT'
CREATE TABLE `#DB_NAME#`.`pb_handshakes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` VARCHAR(255) NOT NULL,
  `date` DATETIME NOT NULL,
  `computer` VARCHAR(255) NOT NULL DEFAULT '',
  `ip` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOT
,
<<<'EOT'
CREATE TABLE `#DB_NAME#`.`pb_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(255) NOT NULL,
  `passwd` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `mail` varchar(1024) CHARACTER SET latin1 NOT NULL,
  `sid` varchar(16) DEFAULT NULL,
  `reset_token` varchar(16) DEFAULT NULL,
  `flags` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOT
,
<<<'EOT'
CREATE TABLE `#DB_NAME#`.`pb_access` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sid` varchar(256) NOT NULL DEFAULT '',
  `dn` varchar(1024) NOT NULL DEFAULT '',
  `oid` int(10) unsigned NOT NULL DEFAULT 0,
  `allow_bits` binary(32) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOT
,
<<<'EOT'
CREATE TABLE `#DB_NAME#`.`pb_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `operation` varchar(1024) NOT NULL,
  `params` varchar(4096) NOT NULL,
  `flags` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOT
,
<<<'EOT'
CREATE TABLE `#DB_NAME#`.`pb_config` (
  `uid` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` varchar(8192) NOT NULL DEFAULT '',
  `description` varchar(2048) DEFAULT NULL,
  PRIMARY KEY (`name`,`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOT
,
<<<'EOT'
INSERT INTO `#DB_NAME#`.`pb_config` (`uid`, `name`, `value`, `description`) VALUES(0, 'db_version', 8, 'DB schema version. Do not change!');
EOT
,
<<<'EOT'
INSERT INTO `#DB_NAME#`.`pb_config` (`uid`, `name`, `value`, `description`) VALUES(0, 'map_names_json', '["Floor 1", "Floor 3", "Floor 6", "Floor 14", "Floor 25"]', 'Map names');
EOT
);

$config = <<<'EOT'
<?php
	define('DB_RW_HOST', '#db_host#');
	define('DB_USER', '#db_user#');
	define('DB_PASSWD', '#db_passwd#');
	define('DB_NAME', '#db_name#');
	define('DB_CPAGE', 'utf8');
	define('DB_PREFIX', 'pb_');

	define('APP_LANGUAGE', '#language#');

	define('USE_GSSAPI', #use_gssapi#);

	define('USE_LDAP', TRUE);
	define('LDAP_URI', '#ldap_uri#');
	define('LDAP_USER', '#ldap_user#');
	define('LDAP_PASSWD', '#ldap_passwd#');
	define('LDAP_BASE_DN', '#ldap_base#');
	define('LDAP_USE_SID', #ldap_use_sid#);

	define('MAIL_HOST', '#mail_host#');
	define('MAIL_FROM', '#mail_from#');
	define('MAIL_FROM_NAME', '#mail_from_name#');
	define('MAIL_ADMIN', '#mail_admin#');
	define('MAIL_ADMIN_NAME', '#mail_admin_name#');
	define('MAIL_AUTH', #mail_auth#);
	define('MAIL_LOGIN', '#mail_user#');
	define('MAIL_PASSWD', '#mail_passwd#');
	define('MAIL_SECURE', '#mail_secure#');
	define('MAIL_PORT', #mail_port#);
	define('MAIL_VERIFY_PEER', #mail_verify_peer#);
	define('MAIL_VERIFY_PEER_NAME', #mail_verify_peer_name#);
	define('MAIL_ALLOW_SELF_SIGNED', #mail_allow_self_signed#);

	define('USE_MEMCACHED', #use_memcached#);

	define('WEB_URL', '#web_url#');
	define('WEB_LINK_BASE_PATH', '#pretty_links_base_path#');
	define('USE_PRETTY_LINKS', #use_pretty_links#);
	define('USE_PRETTY_LINKS_FORCE', #use_pretty_links_force#);

	define('LOG_FILE', '#log_file#');

	define('PB_LDAP_FILTER', '#ldap_filter#');

	$g_icons = array('Human', 'Printer', 'Fax (change inc.config.php)');

EOT;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once ROOT_DIR.'libs/PHPMailer/src/Exception.php';
require_once ROOT_DIR.'libs/PHPMailer/src/PHPMailer.php';
require_once ROOT_DIR.'libs/PHPMailer/src/SMTP.php';

class MySQLDB
{
	private $link = NULL;
	public $data = NULL;
	private $error_msg = '';

	function __construct()
	{
		$link = NULL;
		$data = FALSE;
		$error_msg = '';
	}

	function connect($db_host = '', $db_user = '', $db_passwd = '', $db_name = '', $db_cpage = 'utf8')
	{
		$this->link = mysqli_connect($db_host, $db_user, $db_passwd, $db_name);
		if(!$this->link)
		{
			$this->error(mysqli_connect_error());
			return NULL;
		}
		if(!mysqli_set_charset($this->link, $db_cpage))
		{
			$this->error(mysqli_error($this->link));
			mysqli_close($this->link);
			$this->link = NULL;
			return NULL;
		}
		return $this->link;
	}

	public function __destruct()
	{
		$this->data = FALSE;
		$this->disconnect();
	}

	public function select_db($db_name)
	{
		return mysqli_select_db($this->link, $db_name);
	}

	public function select($query)
	{
		$this->data = FALSE;
		if(!$this->link)
		{
			return FALSE;
		}
		$res = mysqli_query($this->link, $query);
		if(!$res)
		{
			$this->error(mysqli_error($this->link));
			return FALSE;
		}
		if(mysqli_num_rows($res) <= 0)
		{
			return FALSE;
		}
		$this->data = array();
		while($row = mysqli_fetch_row($res))
		{
			$this->data[] = $row;
		}
		mysqli_free_result($res);
		return TRUE;
	}
	public function put($query)
	{
		if(!$this->link)
		{
			return FALSE;
		}
		$res = mysqli_query($this->link, $query);
		if(!$res)
		{
			$this->error(mysqli_error($this->link));
			return FALSE;
		}
		//return mysqli_affected_rows($this->link);
		return TRUE;
	}

	public function last_id()
	{
		return mysqli_insert_id($this->link);
	}

	public function disconnect()
	{
		//$this->data = FALSE;
		$this->error_msg = '';
		if($this->link)
		{
			mysqli_close($this->link);
			$this->link = NULL;
		}
	}

	public function get_last_error()
	{
		return $this->error_msg;
	}

	private function error($str)
	{
		//$this->error_msg = $str;
		throw new Exception($str); //__CLASS__.": ".$str
	}
}

function eh($str)
{
	echo htmlspecialchars($str);
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

function rpv()
{
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

function get_http_xml($url, $user, $passwd, $use_gssapi)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, false);

	if($use_gssapi)
	{
		curl_setopt($ch, CURLOPT_GSSAPI_DELEGATION, CURLGSSAPI_DELEGATION_FLAG);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_GSSNEGOTIATE);
		curl_setopt($ch, CURLOPT_USERPWD, ":");
	}
	else
	{
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_NTLM);
		curl_setopt($ch, CURLOPT_USERPWD, $user.':'.$passwd);
	}

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/atom+xml'));


	$output = curl_exec($ch);
	$result = curl_getinfo($ch);

	//echo $output;
	//log_file($url."\n".$output."\n\n\n");

	curl_close($ch);

	if(intval($result['http_code']) != 200)
	{
		throw new Exception('Unexpected HTTP '.$result['http_code'].' response code!');
		return FALSE;
	}

	$xml = @simplexml_load_string($output);
	if($xml == FALSE)
	{
		throw new Exception('XML parse error!');
		return FALSE;
	}

	return $xml;
}

function build_config($config, $params)
{
	if(empty($params['db_host'])) throw new Exception('Host value not defined!');
	if(empty($params['db_user'])) throw new Exception('Login value not defined!');
	if(empty($params['db_name'])) throw new Exception('DB value not defined!');

	if(empty($params['ldap_uri'])) throw new Exception('LDAP Host value not defined!');
	if(empty($params['ldap_base'])) throw new Exception('LDAP Base DN value not defined!');

	if(empty($params['ldap_filter'])) throw new Exception('LDAP Filter value not defined!');

	if(empty($params['log_file'])) throw new Exception('Log file value not defined!');

	if(empty($params['mail_host'])) throw new Exception('MAIL Host value not defined!');
	if(empty($params['mail_port'])) throw new Exception('MAIL Port value not defined!');
	if(empty($params['mail_from'])) throw new Exception('MAIL From value not defined!');
	if(empty($params['mail_from_name'])) throw new Exception('MAIL From Name value not defined!');
	if(empty($params['mail_admin'])) throw new Exception('MAIL Admin value not defined!');
	if(empty($params['mail_admin_name'])) throw new Exception('MAIL Admin Name value not defined!');

	$mail_verify_peer = (!empty($params['mail_verify_peer']) && intval($params['mail_verify_peer']));
	$mail_verify_peer_name = (!empty($params['mail_verify_peer_name']) && intval($params['mail_verify_peer_name']));
	$mail_allow_self_signed = (!empty($params['mail_allow_self_signed']) && intval($params['mail_allow_self_signed']));

	if(empty($params['web_url'])) throw new Exception('Web URL not defined!');
	if(empty($params['language'])) throw new Exception('Language not selected!');

	if(!empty($params['use_gssapi']) && intval($params['use_gssapi']))
	{
		$use_gssapi = TRUE;
	}
	else
	{
		$use_gssapi = FALSE;
		if(empty($params['ldap_user'])) throw new Exception('LDAP User value not defined!');
		if(empty($params['ldap_passwd'])) throw new Exception('LDAP Password value not defined!');
	}

	return str_replace(
		array(
			'#db_host#',
			'#db_user#',
			'#db_passwd#',
			'#db_name#',
			'#use_gssapi#',
			'#ldap_uri#',
			'#ldap_user#',
			'#ldap_passwd#',
			'#ldap_base#',
			'#ldap_use_sid#',
			'#mail_host#',
			'#mail_port#',
			'#mail_user#',
			'#mail_passwd#',
			'#mail_secure#',
			'#mail_admin#',
			'#mail_admin_name#',
			'#mail_from#',
			'#mail_from_name#',
			'#mail_verify_peer#',
			'#mail_verify_peer_name#',
			'#mail_allow_self_signed#',
			'#ldap_filter#',
			'#web_url#',
			'#pretty_links_base_path#',
			'#mail_auth#',
			'#use_memcached#',
			'#use_pretty_links#',
			'#use_pretty_links_force#',
			'#log_file#',
			'#language#'
		),
		array(
			sql_escape(@$params['db_host']),
			sql_escape(@$params['db_user']),
			sql_escape(@$params['db_passwd']),
			sql_escape(@$params['db_name']),
			$use_gssapi?'TRUE':'FALSE',
			sql_escape(@$params['ldap_uri']),
			sql_escape(@$params['ldap_user']),
			sql_escape(@$params['ldap_passwd']),
			sql_escape(@$params['ldap_base']),
			intval(@$params['ldap_use_sid'])?'TRUE':'FALSE',
			sql_escape(@$params['mail_host']),
			sql_escape(@$params['mail_port']),
			sql_escape(@$params['mail_user']),
			sql_escape(@$params['mail_passwd']),
			sql_escape(@$params['mail_secure']),
			sql_escape(@$params['mail_admin']),
			sql_escape(@$params['mail_admin_name']),
			sql_escape(@$params['mail_from']),
			sql_escape(@$params['mail_from_name']),
			$mail_verify_peer?'TRUE':'FALSE',
			$mail_verify_peer_name?'TRUE':'FALSE',
			$mail_allow_self_signed?'TRUE':'FALSE',
			sql_escape(@$params['ldap_filter']),
			sql_escape(@$params['web_url']),
			sql_escape(@$params['pretty_links_base_path']),
			empty($params['mail_user'])?'FALSE':'TRUE',
			intval(@$params['use_memcached'])?'TRUE':'FALSE',
			intval(@$params['use_pretty_links'])?'TRUE':'FALSE',
			intval(@$params['use_pretty_links_force'])?'TRUE':'FALSE',
			sql_escape(@$params['log_file']),
			sql_escape(@$params['language'])
		),
		$config
	);
}

	//error_reporting(0);

	if(isset($_GET['action']))
	{
		$action = $_GET['action'];
		try
		{
			header("Content-Type: text/plain; charset=utf-8");

			switch($action)
			{
				case 'check_modules':
				{
					$result_json = array(
						'code' => 0,
						'message' => '',
					);

					foreach($modules as $module)
					{
						if(extension_loaded($module))
						{
							$result_json['message'] .= $module." - OK\n";
						}
						else
						{
							$result_json['code'] = 1;
							$result_json['message'] .= $module." - missing\n";
						}
					}

					echo json_encode($result_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
				}
				exit;
				case 'check_db_conn':
				{
					if(empty($_POST['db_host'])) throw new Exception('Host value not defined!');
					if(empty($_POST['db_root_user'])) throw new Exception('Login value not defined!');

					$db = new MySQLDB();
					$db->connect(@$_POST['db_host'], @$_POST['db_root_user'], @$_POST['db_root_passwd']);
					echo '{"code": 0, "message": "OK"}';
				}
				exit;
				case 'create_db':
				{
					if(empty($_POST['db_host'])) throw new Exception('Host value not defined!');
					if(empty($_POST['db_root_user'])) throw new Exception('Login value not defined!');
					if(empty($_POST['db_name'])) throw new Exception('DB value not defined!');

					$db = new MySQLDB();
					$db->connect(@$_POST['db_host'], @$_POST['db_root_user'], @$_POST['db_root_passwd']);
					//foreach($sql as $query)
					//{
					//	$db->put(str_replace('#DB_NAME#', @$_POST['db'], $query));
					//}
					$db->put('CREATE DATABASE `'.sql_escape(@$_POST['db_name']).'` DEFAULT CHARACTER SET utf8');
					//$db->select_db(@$_POST['db']);
					//$db->put($db_table);

					echo '{"code": 0, "message": "OK"}';
				}
				exit;
				case 'create_tables':
				{
					if(empty($_POST['db_host'])) throw new Exception('Host value not defined!');
					if(empty($_POST['db_root_user'])) throw new Exception('Login value not defined!');
					if(empty($_POST['db_name'])) throw new Exception('DB value not defined!');

					$db = new MySQLDB();
					$db->connect(@$_POST['db_host'], @$_POST['db_root_user'], @$_POST['db_root_passwd']);
					foreach($sql as $query)
					{
						$db->put(str_replace('#DB_NAME#', sql_escape(@$_POST['db_name']), $query));
					}
					//$db->put('CREATE DATABASE `'.@$_POST['db'].'` DEFAULT CHARACTER SET utf8');
					//$db->select_db(@$_POST['db']);
					//$db->put($db_table);

					echo '{"code": 0, "message": "OK"}';
				}
				exit;
				case 'create_db_user':
				{
					if(empty($_POST['db_host'])) throw new Exception('Host value not defined!');
					if(empty($_POST['db_root_user'])) throw new Exception('Login value not defined!');
					if(empty($_POST['db_name'])) throw new Exception('DB value not defined!');
					if(empty($_POST['db_user'])) throw new Exception('Login value not defined!');

					$db = new MySQLDB();
					$db->connect(@$_POST['db_host'], @$_POST['db_root_user'], @$_POST['db_root_passwd']);
					$db->put("CREATE USER '".sql_escape(@$_POST['db_user'])."'@'%' IDENTIFIED BY '".sql_escape(@$_POST['db_passwd'])."'");

					echo '{"code": 0, "message": "OK"}';
				}
				exit;
				case 'grant_access':
				{
					if(empty($_POST['db_host'])) throw new Exception('Host value not defined!');
					if(empty($_POST['db_root_user'])) throw new Exception('Login value not defined!');
					if(empty($_POST['db_name'])) throw new Exception('DB value not defined!');
					if(empty($_POST['db_user'])) throw new Exception('Login value not defined!');

					$db = new MySQLDB();
					$db->connect(@$_POST['db_host'], @$_POST['db_root_user'], @$_POST['db_root_passwd']);
					$db->put("GRANT ALL PRIVILEGES ON ".sql_escape(@$_POST['db_name']).".* TO '".sql_escape(@$_POST['db_user'])."'@'%'");
					$db->put("FLUSH PRIVILEGES");

					echo '{"code": 0, "message": "OK"}';
				}
				exit;
				case 'check_ldap':
				{
					if(empty($_POST['ldap_uri'])) throw new Exception('LDAP URI value not defined!');
					if(empty($_POST['ldap_base'])) throw new Exception('LDAP Base DN value not defined!');
					if(empty($_POST['ldap_filter'])) throw new Exception('LDAP Filter value not defined!');

					if(!empty($_POST['use_gssapi']) && intval($_POST['use_gssapi']))
					{
						$use_gssapi = TRUE;
					}
					else
					{
						$use_gssapi = FALSE;
						if(empty($_POST['ldap_user'])) throw new Exception('LDAP User value not defined!');
						if(empty($_POST['ldap_passwd'])) throw new Exception('LDAP Password value not defined!');
					}

					$ldap = ldap_connect(@$_POST['ldap_uri']);
					if($ldap)
					{
						ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
						ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

						if($use_gssapi)
						{
							$bind = @ldap_sasl_bind($ldap, NULL, NULL, 'GSSAPI');
						}
						else
						{
							$bind = ldap_bind($ldap, @$_POST['ldap_user'], @$_POST['ldap_passwd']);
						}

						if($bind)
						{
							$sr = ldap_search($ldap, @$_POST['ldap_base'], @$_POST['ldap_filter'], array('samaccountname'));
							if($sr)
							{
								echo '{"code": 0, "message": "OK (Entries founded: '.ldap_count_entries($ldap, $sr).')"}';
								ldap_free_result($sr);
								exit;
							}
						}
					}
					throw new Exception(ldap_error($ldap));
				}
				exit;
				//*
				case 'check_mail':
				{
					if(empty($_POST['mail_host'])) throw new Exception('MAIL Host value not defined!');
					if(empty($_POST['mail_port'])) throw new Exception('MAIL Port value not defined!');
					if(empty($_POST['mail_from'])) throw new Exception('MAIL From value not defined!');
					if(empty($_POST['mail_from_name'])) throw new Exception('MAIL From Name value not defined!');
					if(empty($_POST['mail_admin'])) throw new Exception('MAIL Admin value not defined!');
					if(empty($_POST['mail_admin_name'])) throw new Exception('MAIL Admin Name value not defined!');

					$mail_verify_peer = (!empty($_POST['mail_verify_peer']) && intval($_POST['mail_verify_peer']));
					$mail_verify_peer_name = (!empty($_POST['mail_verify_peer_name']) && intval($_POST['mail_verify_peer_name']));
					$mail_allow_self_signed = (!empty($_POST['mail_allow_self_signed']) && intval($_POST['mail_allow_self_signed']));

					//require_once 'libs/PHPMailer/PHPMailerAutoload.php';
					//require_once(ROOT_DIR.DIRECTORY_SEPARATOR.'libs/PHPMailer/class.phpmailer.php');
					//require_once(ROOT_DIR.DIRECTORY_SEPARATOR.'libs/PHPMailer/class.smtp.php');

					$mail = new PHPMailer;

					$mail->isSMTP();
					$mail->Host = @$_POST['mail_host'];
					$mail->SMTPAuth = !empty($_POST['mail_user']);
					if($mail->SMTPAuth)
					{
						$mail->Username = @$_POST['mail_user'];
						$mail->Password = @$_POST['mail_passwd'];
					}

					$mail->SMTPOptions = array(
						'ssl' => array(
							'verify_peer' => $mail_verify_peer,
							'verify_peer_name' => $mail_verify_peer_name,
							'allow_self_signed' => $mail_allow_self_signed
						)
					);

					$mail->SMTPSecure = @$_POST['mail_secure'];
					$mail->Port = @$_POST['mail_port'];

					$mail->setFrom(@$_POST['mail_from'], @$_POST['mail_from_name']);
					$mail->addAddress(@$_POST['mail_admin'], @$_POST['mail_admin_name']);

					$mail->isHTML(true);

					$mail->Subject = 'Test message';
					$mail->Body    = 'This is a test message';
					$mail->AltBody = 'This is a test message';

					if(!$mail->send())
					{
						throw new Exception($mail->ErrorInfo);
					}

					echo '{"code": 0, "message": "OK"}';
				}
				exit;
				//*/
				case 'create_admin_account':
				{
					if(empty($_POST['db_host'])) throw new Exception('Host value not defined!');
					if(empty($_POST['db_user'])) throw new Exception('Login value not defined!');
					if(empty($_POST['db_name'])) throw new Exception('DB value not defined!');
					if(empty($_POST['admin_user'])) throw new Exception('Login value not defined!');
					if(empty($_POST['mail_admin'])) throw new Exception('MAIL Admin value not defined!');

					$db = new MySQLDB();
					$db->connect(@$_POST['db_host'], @$_POST['db_root_user'], @$_POST['db_root_passwd'], @$_POST['db_name']);
					$db->put(rpv("
						INSERT
							INTO pb_users (login, passwd, mail, flags)
							VALUES ({s0}, MD5({s1}), {s2}, 0x0008)
						-- ON DUPLICATE KEY UPDATE
						--	SET passwd = MD5({s1}),
						--	mail = {s2}
						",
						@$_POST['admin_user'],
						@$_POST['admin_passwd'].'UserAuth',
						@$_POST['mail_admin']
					));

					echo '{"code": 0, "message": "OK"}';
				}
				exit;
				case 'save_config':
				{
					if(@file_put_contents(ROOT_DIR.'inc.config.php', build_config($config, $_POST)) === FALSE)
					{
						throw new Exception('Save config error. Check write permissions to '.ROOT_DIR);
					}

					echo '{"code": 0, "message": "OK"}';
				}
				exit;
				case 'download_config':
				{
					header("Content-Disposition: attachment; filename=\"inc.config.php\"; filename*=utf-8''inc.config.php");
					echo build_config($config, $_POST);
				}
				exit;
				case 'remove_self':
				{
					if(!unlink('install.php'))
					{
						throw new Exception("FAILED");
					}
					echo '{"code": 0, "message": "OK"}';
				}
				exit;
			}
		}
		catch(Exception $e)
		{
			echo '{"code": 1, "message": "'.json_escape($e->getMessage()).'"}';
			exit;
		}
	}

	header("Content-Type: text/html; charset=utf-8");

	$pretty_links_base_path = preg_replace('#/[^/]+$#', '/', $_SERVER['REQUEST_URI']);
	$web_url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$pretty_links_base_path;
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Installation script</title>
		<meta charset="utf-8">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<style type="text/css">
			html {
				font-family: sans-serif;
				-webkit-text-size-adjust: 100%;
				-ms-text-size-adjust: 100%
			}
			body {
				margin: 0
			}
			button,
			input,
			select {
				margin: 0;
				font: inherit;
				color: inherit
			}
			button {
				overflow: visible
			}
			button,
			select {
				text-transform: none
			}
			button {
				-webkit-appearance: button;
				cursor: pointer
			}
			button::-moz-focus-inner,
			input::-moz-focus-inner {
				padding: 0;
				border: 0
			}
			input {
				line-height: normal
			}

			@media print {
				*,
				:after,
				:before {
					color: #000!important;
					text-shadow: none!important;
					background: 0 0!important;
					-webkit-box-shadow: none!important;
					box-shadow: none!important
				}
				h3 {
					orphans: 3;
					widows: 3
				}
				h3 {
					page-break-after: avoid
				}
			}
			@font-face {
				font-family: 'Glyphicons Halflings';
				src: url(../fonts/glyphicons-halflings-regular.eot);
				src: url(../fonts/glyphicons-halflings-regular.eot?#iefix) format('embedded-opentype'), url(../fonts/glyphicons-halflings-regular.woff2) format('woff2'), url(../fonts/glyphicons-halflings-regular.woff) format('woff'), url(../fonts/glyphicons-halflings-regular.ttf) format('truetype'), url(../fonts/glyphicons-halflings-regular.svg#glyphicons_halflingsregular) format('svg')
			}
			* {
				-webkit-box-sizing: border-box;
				-moz-box-sizing: border-box;
				box-sizing: border-box
			}
			:after,
			:before {
				-webkit-box-sizing: border-box;
				-moz-box-sizing: border-box;
				box-sizing: border-box
			}
			html {
				font-size: 10px;
				-webkit-tap-highlight-color: rgba(0, 0, 0, 0)
			}
			body {
				font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
				font-size: 14px;
				line-height: 1.42857143;
				color: #333;
				background-color: #fff
			}
			button,
			input,
			select {
				font-family: inherit;
				font-size: inherit;
				line-height: inherit
			}
			h3 {
				font-family: inherit;
				font-weight: 500;
				line-height: 1.1;
				color: inherit
			}
			h3 {
				margin-top: 20px;
				margin-bottom: 10px
			}
			h3 {
				font-size: 24px
			}
			.container {
				padding-right: 15px;
				padding-left: 15px;
				margin-right: auto;
				margin-left: auto
			}
			@media (min-width: 768px) {
				.container {
					width: 750px
				}
			}
			@media (min-width: 992px) {
				.container {
					width: 970px
				}
			}
			@media (min-width: 1200px) {
				.container {
					width: 1170px
				}
			}
			.col-sm-2,
			.col-sm-5 {
				position: relative;
				min-height: 1px;
				padding-right: 15px;
				padding-left: 15px
			}
			@media (min-width: 768px) {
				.col-sm-2,
				.col-sm-5 {
					float: left
				}
				.col-sm-5 {
					width: 41.66666667%
				}
				.col-sm-2 {
					width: 16.66666667%
				}
				.col-sm-offset-2 {
					margin-left: 16.66666667%
				}
			}
			label {
				display: inline-block;
				max-width: 100%;
				margin-bottom: 5px;
				font-weight: 700
			}
			.form-control {
				display: block;
				width: 100%;
				height: 34px;
				padding: 6px 12px;
				font-size: 14px;
				line-height: 1.42857143;
				color: #555;
				background-color: #fff;
				background-image: none;
				border: 1px solid #ccc;
				border-radius: 4px;
				-webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);
				box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);
				-webkit-transition: border-color ease-in-out .15s, -webkit-box-shadow ease-in-out .15s;
				-o-transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
				transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s
			}
			.form-control:focus {
				border-color: #66afe9;
				outline: 0;
				-webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075), 0 0 8px rgba(102, 175, 233, .6);
				box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075), 0 0 8px rgba(102, 175, 233, .6)
			}
			.form-control::-moz-placeholder {
				color: #999;
				opacity: 1
			}
			.form-control:-ms-input-placeholder {
				color: #999
			}
			.form-control::-webkit-input-placeholder {
				color: #999
			}
			.form-control::-ms-expand {
				background-color: transparent;
				border: 0
			}
			.form-group {
				margin-bottom: 15px
			}
			.form-horizontal .form-group {
				margin-right: -15px;
				margin-left: -15px
			}
			@media (min-width: 768px) {
				.form-horizontal .control-label {
					padding-top: 7px;
					margin-bottom: 0;
					text-align: right
				}
			}
			.btn {
				display: inline-block;
				padding: 6px 12px;
				margin-bottom: 0;
				font-size: 14px;
				font-weight: 400;
				line-height: 1.42857143;
				text-align: center;
				white-space: nowrap;
				vertical-align: middle;
				-ms-touch-action: manipulation;
				touch-action: manipulation;
				cursor: pointer;
				-webkit-user-select: none;
				-moz-user-select: none;
				-ms-user-select: none;
				user-select: none;
				background-image: none;
				border: 1px solid transparent;
				border-radius: 4px
			}
			.btn:active:focus,
			.btn:focus {
				outline: 5px auto -webkit-focus-ring-color;
				outline-offset: -2px
			}
			.btn:focus,
			.btn:hover {
				color: #333;
				text-decoration: none
			}
			.btn:active {
				background-image: none;
				outline: 0;
				-webkit-box-shadow: inset 0 3px 5px rgba(0, 0, 0, .125);
				box-shadow: inset 0 3px 5px rgba(0, 0, 0, .125)
			}
			.btn-primary {
				color: #fff;
				background-color: #337ab7;
				border-color: #2e6da4
			}
			.btn-primary:focus {
				color: #fff;
				background-color: #286090;
				border-color: #122b40
			}
			.btn-primary:hover {
				color: #fff;
				background-color: #286090;
				border-color: #204d74
			}
			.btn-primary:active {
				color: #fff;
				background-color: #286090;
				border-color: #204d74
			}
			.btn-primary:active:focus,
			.btn-primary:active:hover {
				color: #fff;
				background-color: #204d74;
				border-color: #122b40
			}
			.btn-primary:active {
				background-image: none
			}
			.alert {
				padding: 15px;
				margin-bottom: 20px;
				border: 1px solid transparent;
				border-radius: 4px
			}
			.alert-danger {
				color: #a94442;
				background-color: #f2dede;
				border-color: #ebccd1
			}
			.alert-success{
				color: #3c763d;
				background-color: #dff0d8;
				border-color: #d6e9c6
			}
			.container:after,
			.container:before,
			.form-horizontal .form-group:after,
			.form-horizontal .form-group:before {
				display: table;
				content: " "
			}
			.container:after,
			.form-horizontal .form-group:after {
				clear: both
			}
			@-ms-viewport {
				width: device-width
			}

/*--------- SLIDER --------*/
.switch {
  position: relative;
  display: inline-block;
  width: 90px;
  height: 34px;
}

.switch input {display:none;}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ca2222;
  -webkit-transition: .4s;
  transition: .4s;
   border-radius: 34px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: #2ab934;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(55px);
}

.slider:after
{
 content:'OFF';
 color: white;
 display: block;
 position: absolute;
 transform: translate(-50%,-50%);
 top: 50%;
 left: 50%;
 font-size: 10px;
 font-family: Verdana, sans-serif;
}

input:checked + .slider:after
{
  content:'ON';
}

/*--------- END --------*/
		</style>
		<script type="text/javascript">
			function gi(name)
			{
				return document.getElementById(name);
			}

			function json2url(data)
			{
				return Object.keys(data).map(
					function(k)
					{
						return encodeURIComponent(k) + '=' + encodeURIComponent(data[k])
					}
				).join('&');
			}

			function f_xhr()
			{
				try { return new XMLHttpRequest(); } catch(e) {}
				try { return new ActiveXObject("Msxml3.XMLHTTP"); } catch(e) {}
				try { return new ActiveXObject("Msxml2.XMLHTTP.6.0"); } catch(e) {}
				try { return new ActiveXObject("Msxml2.XMLHTTP.3.0"); } catch(e) {}
				try { return new ActiveXObject("Msxml2.XMLHTTP"); } catch(e) {}
				try { return new ActiveXObject("Microsoft.XMLHTTP"); } catch(e) {}
				console.log("ERROR: XMLHttpRequest undefined");
				return null;
			}

			function f_http(url, _f_callback, _callback_params, content_type, data)
			{
				var f_callback = null;
				var callback_params = null;

				if(typeof _f_callback !== 'undefined') f_callback = _f_callback;
				if(typeof _callback_params !== 'undefined') callback_params = _callback_params;
				if(typeof content_type === 'undefined') content_type = null;
				if(typeof data === 'undefined') data = null;

				var xhr = f_xhr();
				if(!xhr)
				{
					if(f_callback)
					{
						f_callback({code: 1, message: "AJAX error: XMLHttpRequest unsupported"}, callback_params);
					}

					return false;
				}

				xhr.open((content_type || data)?"post":"get", url, true);
				xhr.onreadystatechange = function()
				{
					if(xhr.readyState == 4)
					{
						var result;
						if(xhr.status == 200)
						{
							try
							{
								result = JSON.parse(xhr.responseText);
							}
							catch(e)
							{
								result = {code: 1, message: "Response: "+xhr.responseText};
							}
						}
						else
						{
							result = {code: 1, message: "AJAX error code: "+xhr.status};
						}

						if(f_callback)
						{
							f_callback(result, callback_params);
						}
					}
				};

				if(content_type)
				{
					xhr.setRequestHeader('Content-Type', content_type);
				}

				xhr.send(data);

				return true;
			}

			function f_send_form(action)
			{
				var form_data = {};
				var el = gi('uform-fields');
				for(i = 0; i < el.elements.length; i++)
				{
					if(el.elements[i].name)
					{
						if(el.elements[i].type == 'checkbox')
						{
							if(el.elements[i].checked)
							{
								form_data[el.elements[i].name] = el.elements[i].value;
							}
						}
						else if(el.elements[i].type == 'select-one')
						{
							if(el.elements[i].selectedIndex != -1)
							{
								form_data[el.elements[i].name] = el.elements[i].value;
							}
						}
						else
						{
							form_data[el.elements[i].name] = el.elements[i].value;
						}
					}
				}

				//alert(json2url(form_data));
				//return;

				gi('result_'+action).textContent = 'Loading...';
				gi('result_'+action).style.display = 'block';
				f_http('install.php?action=' + action,
					function(data, action)
					{
						if(data.code)
						{
							gi('result_'+action).classList.remove('alert-success');
							gi('result_'+action).classList.add('alert-danger');
						}
						else
						{
							gi('result_'+action).classList.remove('alert-danger');
							gi('result_'+action).classList.add('alert-success');
						}
						gi('result_'+action).textContent = data.message;
					},
					action,
					'application/x-www-form-urlencoded',
					json2url(form_data)
				);

				return false;
			}

			function f_download_config()
			{
				gi('uform-fields').submit();
			}

			function f_remove_self(id)
			{
				f_send_form('remove_self');
			}
		</script>
	</head>
	<body>
		<div class="container">
		<div class="form-horizontal">
		<form id="uform-fields" action="?action=download_config" method="post" target="_blank">
			<?php $n = 1 ?>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-5">
					<h3>Requirements</h3>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-5">
					<button type="button" class="btn btn-primary" onclick="f_send_form('check_modules');"><?php eh($n++) ?>. Check loaded PHP modules</button>
					<pre id="result_check_modules" class="alert alert-danger" style="display: none"></pre>
				</div>
			</div>

			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-5">
					<h3>Language settings</h3>
				</div>
			</div>
			<div class="form-group">
				<label for="language" class="control-label col-sm-2">Language:</label>
				<div class="col-sm-5">
					<select id="language" name="language" class="form-control">
						<?php
							$fileList = glob("languages/*.php");
							foreach ($fileList as $lanFile) {
								$path_parts = pathinfo($lanFile);
								echo '<option value="'.$path_parts['filename'].'">'.$path_parts['filename'].'</option>';
							};
						?>
					</select>
				</div>
			</div>

			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-5">
					<h3>MySQL settings</h3>
				</div>
			</div>
			<div class="form-group">
				<label for="db_host" class="control-label col-sm-2">Host:</label>
				<div class="col-sm-5">
					<input id="db_host" name="db_host" class="form-control" type="text" value="localhost" />
				</div>
			</div>
			<div class="form-group">
				<label for="db_root_user" class="control-label col-sm-2">Login:</label>
				<div class="col-sm-5">
					<input id="db_root_user" name="db_root_user" class="form-control" type="text" value="root" />
				</div>
			</div>
			<div class="form-group">
				<label for="db_root_passwd" class="control-label col-sm-2">Password:</label>
				<div class="col-sm-5">
					<input id="db_root_passwd" name="db_root_passwd" class="form-control" type="password" value="" />
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-5">
					<button type="button" class="btn btn-primary" onclick="f_send_form('check_db_conn');"><?php eh($n++) ?>. Check DB connection</button>
					<div id="result_check_db_conn" class="alert alert-danger" style="display: none"></div>
				</div>
			</div>
			<div class="form-group">
				<label for="db_name" class="control-label col-sm-2">DB name:</label>
				<div class="col-sm-5">
					<input id="db_name" name="db_name" class="form-control" type="text" value="pb" />
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-5">
					<button type="button" class="btn btn-primary" onclick="f_send_form('create_db');"><?php eh($n++) ?>. Create database</button>
					<div id="result_create_db" class="alert alert-danger" style="display: none"></div>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-5">
					<button type="button" class="btn btn-primary" onclick="f_send_form('create_tables');"><?php eh($n++) ?>. Create tables</button>
					<div id="result_create_tables" class="alert alert-danger" style="display: none"></div>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-5">
					<h3>New MySQL DB user or enter existing</h3>
				</div>
			</div>
			<div class="form-group">
				<label for="db_user" class="control-label col-sm-2">Login:</label>
				<div class="col-sm-5">
					<input id="db_user" name="db_user" class="form-control" type="text" value="dbuser" />
				</div>
			</div>
			<div class="form-group">
				<label for="db_passwd" class="control-label col-sm-2">Password:</label>
				<div class="col-sm-5">
					<input id="db_passwd" name="db_passwd" class="form-control" type="password" value="" />
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-5">
					<button type="button" class="btn btn-primary" onclick="f_send_form('create_db_user');"><?php eh($n++) ?>. Create DB user</button>
					<div id="result_create_db_user" class="alert alert-danger" style="display: none"></div>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-5">
					<button type="button" class="btn btn-primary" onclick="f_send_form('grant_access');"><?php eh($n++) ?>. Grant access to database</button>
					<div id="result_grant_access" class="alert alert-danger" style="display: none"></div>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-5">
					<h3>Kerberos (keytab required)</h3>
				</div>
			</div>
			<div class="form-group">
				<label for="use_gssapi" class="control-label col-sm-2">Use GSSAPI for LDAP and CURL:</label>
				<div class="col-sm-5">
					<label class="switch"><input id="use_gssapi" name="use_gssapi" class="form-control" type="checkbox" value="1" /><div class="slider round"></div></label>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-5">
					<h3>LDAP settings</h3>
				</div>
			</div>
			<div class="form-group">
				<label for="ldap_uri" class="control-label col-sm-2">LDAP URI:</label>
				<div class="col-sm-5">
					<input id="ldap_uri" name="ldap_uri" class="form-control" type="text" value="ldap://dc-01.contoso.com ldap://dc-02.contoso.com:389 ldaps://dc-03.contoso.com" />
				</div>
			</div>
			<div class="form-group">
				<label for="ldap_user" class="control-label col-sm-2">User:</label>
				<div class="col-sm-5">
					<input id="ldap_user" name="ldap_user" class="form-control" type="text" value="domain\user" />
				</div>
			</div>
			<div class="form-group">
				<label for="ldap_pwd" class="control-label col-sm-2">Password:</label>
				<div class="col-sm-5">
					<input id="ldap_passwd" name="ldap_passwd" class="form-control" type="password" value="" />
				</div>
			</div>
			<div class="form-group">
				<label for="ldap_base" class="control-label col-sm-2">Base DN:</label>
				<div class="col-sm-5">
					<input id="ldap_base" name="ldap_base" class="form-control" type="text" value="DC=company,DC=local" />
				</div>
			</div>
			<div class="form-group">
				<label for="ldap_filter" class="control-label col-sm-2">LDAP Filter:</label>
				<div class="col-sm-5">
					<input id="ldap_filter" name="ldap_filter" class="form-control" type="text" value="(&amp;(objectCategory=person)(objectClass=user)(sAMAccountType=805306368)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))" />
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-5">
					<button type="button" class="btn btn-primary" onclick="f_send_form('check_ldap');"><?php eh($n++) ?>. Check LDAP connection</button>
					<div id="result_check_ldap" class="alert alert-danger" style="display: none"></div>
				</div>
			</div>
<!-- -->
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-5">
					<h3>Mail settings</h3>
				</div>
			</div>
			<div class="form-group">
				<label for="mail_host" class="control-label col-sm-2">Host:</label>
				<div class="col-sm-5">
					<input id="mail_host" name="mail_host" class="form-control" type="text" value="smtp.example.com" />
				</div>
			</div>
			<div class="form-group">
				<label for="mail_port" class="control-label col-sm-2">Port:</label>
				<div class="col-sm-5">
					<input id="mail_port" name="mail_port" class="form-control" type="text" value="25" />
				</div>
			</div>
			<div class="form-group">
				<label for="mail_user" class="control-label col-sm-2">User:</label>
				<div class="col-sm-5">
					<input id="mail_user" name="mail_user" class="form-control" type="text" value="robot@example.com" />
				</div>
			</div>
			<div class="form-group">
				<label for="mail_passwd" class="control-label col-sm-2">Password:</label>
				<div class="col-sm-5">
					<input id="mail_passwd" name="mail_passwd" class="form-control" type="password" value="" />
				</div>
			</div>
			<div class="form-group">
				<label for="mail_from" class="control-label col-sm-2">From address:</label>
				<div class="col-sm-5">
					<input id="mail_from" name="mail_from" class="form-control" type="text" value="robot@example.com" />
				</div>
			</div>
			<div class="form-group">
				<label for="mail_from_name" class="control-label col-sm-2">From name:</label>
				<div class="col-sm-5">
					<input id="mail_from_name" name="mail_from_name" class="form-control" type="text" value="Robot" />
				</div>
			</div>
			<div class="form-group">
				<label for="mail_admin" class="control-label col-sm-2">Admin address:</label>
				<div class="col-sm-5">
					<input id="mail_admin" name="mail_admin" class="form-control" type="text" value="admin@example.com" />
				</div>
			</div>
			<div class="form-group">
				<label for="mail_admin_name" class="control-label col-sm-2">Admin name:</label>
				<div class="col-sm-5">
					<input id="mail_admin_name" name="mail_admin_name" class="form-control" type="text" value="Admin" />
				</div>
			</div>
			<div class="form-group">
				<label for="mail_secure" class="control-label col-sm-2">Secure:</label>
				<div class="col-sm-5">
					<select id="mail_secure" class="form-control">
						<option value="" selected="selected">None</option>
						<option value="tls">TLS</option>
						<option value="ssl">SSL</option>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label for="mail_verify_peer" class="control-label col-sm-2">Verify peer:</label>
				<div class="col-sm-5">
					<label class="switch"><input id="mail_verify_peer" name="mail_verify_peer" class="form-control" type="checkbox" value="1" checked="checked"/><div class="slider round"></div></label>
				</div>
			</div>
			<div class="form-group">
				<label for="mail_verify_peer_name" class="control-label col-sm-2">Verify peer name:</label>
				<div class="col-sm-5">
					<label class="switch"><input id="mail_verify_peer_name" name="mail_verify_peer_name" class="form-control" type="checkbox" value="1" checked="checked" /><div class="slider round"></div></label>
				</div>
			</div>
			<div class="form-group">
				<label for="mail_allow_self_signed" class="control-label col-sm-2">Allow self signed certificate:</label>
				<div class="col-sm-5">
					<label class="switch"><input id="mail_allow_self_signed" name="mail_allow_self_signed" class="form-control" type="checkbox" value="1" /><div class="slider round"></div></label>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-5">
					<button type="button" class="btn btn-primary" onclick="f_send_form('check_mail');"><?php eh($n++) ?>. Check mail connection</button>
					<div id="result_check_mail" class="alert alert-danger" style="display: none"></div>
				</div>
			</div>
<!-- -->
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-5">
					<h3>Local admin account</h3>
				</div>
			</div>
			<div class="form-group">
				<label for="admin_user" class="control-label col-sm-2">Login:</label>
				<div class="col-sm-5">
					<input id="admin_user" name="admin_user" class="form-control" type="text" value="admin" />
				</div>
			</div>
			<div class="form-group">
				<label for="admin_passwd" class="control-label col-sm-2">Password:</label>
				<div class="col-sm-5">
					<input id="admin_passwd" name="admin_passwd" class="form-control" type="password" value="" />
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-5">
					<button type="button" class="btn btn-primary" onclick="f_send_form('create_admin_account');"><?php eh($n++) ?>. Create admin account</button>
					<div id="result_create_admin_account" class="alert alert-danger" style="display: none"></div>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-5">
					<h3>Misc settings</h3>
				</div>
			</div>
			<div class="form-group">
				<label for="web_url" class="control-label col-sm-2">URL (with trailing slash):</label>
				<div class="col-sm-5">
					<input id="web_url" name="web_url" class="form-control" type="text" value="<?php eh($web_url) ?>" />
				</div>
			</div>
			<div class="form-group">
				<label for="pretty_links_base_path" class="control-label col-sm-2">Links base path (with leading and trailing slash):</label>
				<div class="col-sm-5">
					<input id="pretty_links_base_path" name="pretty_links_base_path" class="form-control" type="text" value="<?php eh($pretty_links_base_path) ?>" />
				</div>
			</div>
			<div class="form-group">
				<label for="use_pretty_links" class="control-label col-sm-2">Use pretty links (mod_rewrite required):</label>
				<div class="col-sm-5">
					<label class="switch"><input id="use_pretty_links" name="use_pretty_links" class="form-control" type="checkbox" value="1" /><div class="slider round"></div></label>
				</div>
			</div>
			<div class="form-group">
				<label for="use_pretty_links_force" class="control-label col-sm-2">Use pretty links force (if used nginx rewrite):</label>
				<div class="col-sm-5">
					<label class="switch"><input id="use_pretty_links_force" name="use_pretty_links_force" class="form-control" type="checkbox" value="1" /><div class="slider round"></div></label>
				</div>
			</div>
			<div class="form-group">
				<label for="use_memcached" class="control-label col-sm-2">Use memcached:</label>
				<div class="col-sm-5">
					<label class="switch"><input id="use_memcached" name="use_memcached" class="form-control" type="checkbox" value="1" /><div class="slider round"></div></label>
				</div>
			</div>
			<div class="form-group">
				<label for="ldap_use_sid" class="control-label col-sm-2">LDAP use SID for access groups (otherwise DN):</label>
				<div class="col-sm-5">
					<label class="switch"><input id="ldap_use_sid" name="ldap_use_sid" class="form-control" type="checkbox" value="1" /><div class="slider round"></div></label>
				</div>
			</div>
			<div class="form-group">
				<label for="log_file" class="control-label col-sm-2">Log file:</label>
				<div class="col-sm-5">
					<input id="log_file" name="log_file" class="form-control" type="text" value="/var/log/pb/pb.log" />
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-5">
					<button type="button" class="btn btn-primary" onclick="f_send_form('save_config');"><?php eh($n++) ?>. Save config</button> or <button type="button" class="btn btn-primary" onclick="f_download_config();">Download config</button>
					<div id="result_save_config" class="alert alert-danger" style="display: none"></div>
					<div id="result_download_config" class="alert alert-danger" style="display: none"></div>
				</div>
			</div>
		</form>
		</div>
		</div>
	</body>
</html>
