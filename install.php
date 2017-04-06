<?php

class MySQLDB
{
	private $link = NULL;
	public $data = NULL;
	private $error_msg = "";
	function __construct()
	{
		$link = NULL;
		$data = FALSE;
		$error_msg = "";
	}
	function connect($db_host = "", $db_user = "", $db_passwd = "", $db_name = "", $db_cpage = "utf8")
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
		$this->error_msg = "";
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

$db_table = <<<EOT
CREATE TABLE  `pb_contacts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `samname` varchar(255) NOT NULL DEFAULT '',
  `fname` varchar(255) NOT NULL DEFAULT '',
  `lname` varchar(255) NOT NULL DEFAULT '',
  `dep` varchar(255) NOT NULL DEFAULT '',
  `org` varchar(255) NOT NULL DEFAULT '',
  `pos` varchar(255) NOT NULL DEFAULT '',
  `pint` varchar(255) NOT NULL DEFAULT '',
  `pcell` varchar(255) NOT NULL DEFAULT '',
  `mail` varchar(255) NOT NULL DEFAULT '',
  `bday` date DEFAULT NULL,
  `mime` varchar(255) NOT NULL DEFAULT '',
  `photo` blob NOT NULL,
  `visible` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOT;

$config = <<<'EOT'
<?php
	define("LDAP_HOST", "dc-01");
	define("LDAP_PORT", 389);
	define("LDAP_USER", "domain\\login");
	define("LDAP_PASSWD", "password");
	define("LDAP_BASE_DN", "DC=domain,DC=local");
	define("LDAP_FILTER", "(&(objectClass=person)(objectClass=user)(sAMAccountType=805306368)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))");
	define("LDAP_ATTRS", "samaccountname,ou,sn,givenname,mail,department,company,title,telephonenumber,mobile,thumbnailphoto");

	define("DB_HOST", "localhost");
	define("DB_USER", "root");
	define("DB_PASSWD", "");
	define("DB_NAME", "pb");
	define("DB_CPAGE", "utf8");
EOT;


	error_reporting(0);
	
	$action = "";
	if(isset($_GET['action']))
	{
		$action = $_GET['action'];
	}

	try
	{
		switch($action)
		{
			case 'check_db':
			{
				header("Content-Type: text/plain; charset=utf-8");
				$db = new MySQLDB();
				$db->connect(@$_POST['host'], @$_POST['user'], @$_POST['pwd']);
				echo '{"result": 0, "status": "OK"}';
			}
			exit;
			case 'create_db':
			{
				header("Content-Type: text/plain; charset=utf-8");

				$db = new MySQLDB();
				$db->connect(@$_POST['host'], @$_POST['user'], @$_POST['pwd']);
				$db->put('CREATE DATABASE `'.@$_POST['db'].'` DEFAULT CHARACTER SET utf8;');
				$db->select_db(@$_POST['db']);
				$db->put($db_table);

				echo '{"result": 0, "status": "OK"}';
			}
			exit;
			case 'create_db_user':
			{
				header("Content-Type: text/plain; charset=utf-8");

				$db = new MySQLDB();
				$db->connect(@$_POST['host'], @$_POST['user'], @$_POST['pwd']);
				$db->put("CREATE USER '".@$_POST['dbuser']."'@'%' IDENTIFIED BY '".@$_POST['dbpwd']."'");

				echo '{"result": 0, "status": "OK"}';
			}
			exit;
			case 'grant_access':
			{
				header("Content-Type: text/plain; charset=utf-8");

				$db = new MySQLDB();
				$db->connect(@$_POST['host'], @$_POST['user'], @$_POST['pwd']);
				$db->put("GRANT ALL PRIVILEGES ON ".@$_POST['db'].".* TO '".@$_POST['dbuser']."'@'%'");
				$db->put("FLUSH PRIVILEGES");
			
				echo '{"result": 0, "status": "OK"}';
			}
			exit;
			case 'save_config':
			{
				header("Content-Type: text/plain; charset=utf-8");
				
				if(file_put_contents('test.txt', $config) === FALSE)
				{
					throw new Exception("Error save file");
				}
				
				echo '{"result": 0, "status": "OK"}';
			}
			exit;
		}
	}
	catch(Exception $e)
	{
		echo '{"result": 1, "status": "'.$e->getMessage().'"}';
		exit;
	}
	
	header("Content-Type: text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Installation script</title>
		<meta charset="utf-8"> 
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link type="text/css" href="templ/bootstrap.min.css" rel="stylesheet" />
		<script type="text/javascript">
			function gi(name)
			{
				return document.getElementById(name);
			}

			if(!XMLHttpRequest.prototype.sendAsBinary) {
				XMLHttpRequest.prototype.sendAsBinary = function(datastr) {
					function byteValue(x)
					{
						return x.charCodeAt(0) & 0xff;
					}
					var ords = Array.prototype.map.call(datastr, byteValue);
					var ui8a = new Uint8Array(ords);
					try {
						this.send(ui8a);
					}
					catch(e) {
						this.send(ui8a.buffer);
					}
				};
			}

			function f_xhr() {
			  if (typeof XMLHttpRequest === 'undefined') {
				XMLHttpRequest = function() {
				  try { return new ActiveXObject("Msxml2.XMLHTTP.6.0"); }
					catch(e) {}
				  try { return new ActiveXObject("Msxml2.XMLHTTP.3.0"); }
					catch(e) {}
				  try { return new ActiveXObject("Msxml2.XMLHTTP"); }
					catch(e) {}
				  try { return new ActiveXObject("Microsoft.XMLHTTP"); }
					catch(e) {}
				  throw new Error("This browser does not support XMLHttpRequest.");
				};
			  }
			  return new XMLHttpRequest();
			}

			function f_post(id, action, data)
			{
				var xhr = f_xhr();
				if (xhr)
				{
					xhr.open("post", "install.php?action="+action, true);
					xhr.onreadystatechange = function(e) {
						if(this.readyState == 4) {
							if(this.status == 200)
							{
								var result = JSON.parse(this.responseText);
								gi("result_"+id).textContent = result.status;
							}
						}
					};
					xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
					//xhr.send("name="+encodeURIComponent(el.value));
					xhr.send(data);
				}

				return false;
			}

			function f_check_db_conn(id)
			{
				gi("result_"+id).textContent = 'Wait...';
				gi("result_"+id).style.display = 'block';
				f_post(id, 'check_db', 'host='+encodeURIComponent(gi('host').value)+'&user='+encodeURIComponent(gi('user_root').value)+'&pwd='+encodeURIComponent(gi('pwd_root').value));
			}

			function f_create_db(id)
			{
				gi("result_"+id).textContent = 'Wait...';
				f_post(id, 'create_db', 'host='+encodeURIComponent(gi('host').value)+'&user='+encodeURIComponent(gi('user_root').value)+'&pwd='+encodeURIComponent(gi('pwd_root').value)
					+'&db='+encodeURIComponent(gi('db_scheme').value));
			}

			function f_create_db_user(id)
			{
				gi("result_"+id).textContent = 'Wait...';
				f_post(id, 'create_db_user', 'host='+encodeURIComponent(gi('host').value)+'&user='+encodeURIComponent(gi('user_root').value)+'&pwd='+encodeURIComponent(gi('pwd_root').value)
					+'&dbuser='+encodeURIComponent(gi('db_user').value)+'&dbpwd='+encodeURIComponent(gi('db_pwd').value));
			}

			function f_grant_access(id)
			{
				gi("result_"+id).textContent = 'Wait...';
				f_post(id, 'grant_access', 'host='+encodeURIComponent(gi('host').value)+'&user='+encodeURIComponent(gi('user_root').value)+'&pwd='+encodeURIComponent(gi('pwd_root').value)
					+'&db='+encodeURIComponent(gi('db_scheme').value)+'&dbuser='+encodeURIComponent(gi('db_user').value)+'&dbpwd='+encodeURIComponent(gi('db_pwd').value));
			}

			function f_save_config(id)
			{
				gi("result_"+id).textContent = 'Wait...';
				f_post(id, "save_config", encodeURIComponent(gi('host').value)+'&db='+encodeURIComponent(gi('db_scheme').value)+'&dbuser='+encodeURIComponent(gi('db_user').value)+'&dbpwd='+encodeURIComponent(gi('db_pwd').value));
			}
		</script>
	</head>
	<body>
		<div class="container">
		<div class="form-horizontal">
			<div class="form-group"> 
				<div class="col-sm-offset-2 col-sm-5">
					<h3>MySQL settings</h3>
				</div>
			</div>
			<div class="form-group">
				<label for="host" class="control-label col-sm-2">Host:</label>
				<div class="col-sm-5"> 
					<input id="host" class="form-control" type="text" value="" />
				</div>
			</div>
			<div class="form-group">
				<label for="user_root" class="control-label col-sm-2">Login:</label>
				<div class="col-sm-5"> 
					<input id="user_root" class="form-control" type="text" value="" />
				</div>
			</div>
			<div class="form-group">
				<label for="pwd_root" class="control-label col-sm-2">Password:</label>
				<div class="col-sm-5"> 
					<input id="pwd_root" class="form-control" type="text" value="" />
				</div>
			</div>
			<div class="form-group"> 
				<div class="col-sm-offset-2 col-sm-5">
					<button type="button" class="btn btn-primary" onclick='f_check_db_conn(1);'>1. Check DB connection</button><div id="result_1" class="alert alert-danger" style="display: none"></div>
				</div>
			</div>
			<div class="form-group">
				<label for="db_scheme" class="control-label col-sm-2">DB name:</label>
				<div class="col-sm-5"> 
					<input id="db_scheme" class="form-control" type="text" value="" />
				</div>
			</div>
			<div class="form-group"> 
				<div class="col-sm-offset-2 col-sm-5">
					<button type="button" class="btn btn-primary" onclick='f_create_db(2);'>2. Create database and tables</button><div id="result_2" class="alert alert-danger" style="display: none"></div>
				</div>
			</div>
			<div class="form-group"> 
				<div class="col-sm-offset-2 col-sm-5">
					<h3>New DB user</h3>
				</div>
			</div>
			<div class="form-group">
				<label for="db_user" class="control-label col-sm-2">Login:</label>
				<div class="col-sm-5"> 
					<input id="db_user" class="form-control" type="text" value="" />
				</div>
			</div>
			<div class="form-group">
				<label for="db_pwd" class="control-label col-sm-2">Password:</label>
				<div class="col-sm-5"> 
					<input id="db_pwd" class="form-control" type="text" value="" />
				</div>
			</div>
			<div class="form-group"> 
				<div class="col-sm-offset-2 col-sm-5">
					<button type="button" class="btn btn-primary" onclick='f_create_db_user(3);'>3. Create DB user</button><div id="result_3" class="alert alert-danger" style="display: none"></div>
				</div>
			</div>
			<div class="form-group"> 
				<div class="col-sm-offset-2 col-sm-5">
					<button type="button" class="btn btn-primary" onclick='f_grant_access(4);'>4. Grant access to database</button><div id="result_4" class="alert alert-danger" style="display: none"></div>
				</div>
			</div>
			<div class="form-group"> 
				<div class="col-sm-offset-2 col-sm-5">
					<h3>LDAP settings</h3>
				</div>
			</div>
			<div class="form-group">
				<label for="ldap_host" class="control-label col-sm-2">Host:</label>
				<div class="col-sm-5"> 
					<input id="ldap_host" class="form-control" type="text" value="" />
				</div>
			</div>
			<div class="form-group"> 
				<div class="col-sm-offset-2 col-sm-5">
					<button type="button" class="btn btn-primary" onclick='f_save_config(5);'>5. Save config</button><div id="result_5" class="alert alert-danger" style="display: none"></div>
				</div>
			</div>
		</div>
		</div>
	</body>
</html>
