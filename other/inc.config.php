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