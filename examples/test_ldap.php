<?php
/*
    LDAP-phonebook - simple LDAP phonebook
    Copyright (C) 2016 Dmitry V. Zimin

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

if(!file_exists('inc.config.php'))
{
	header('Location: install.php');
	exit;
}

	include('inc.config.php');

	header("Content-Type: text/plain; charset=utf-8");

	$ldap = ldap_connect(LDAP_HOST, LDAP_PORT);
	if($ldap)
	{
		ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
		if(ldap_bind($ldap, LDAP_USER, LDAP_PASSWD))
		{
			$data = array();
			$count_updated = 0;
			$count_added = 0;
			$cookie = '';
			do
			{
				ldap_control_paged_result($ldap, 200, true, $cookie);

				$sr = ldap_search($ldap, LDAP_BASE_DN, LDAP_FILTER, explode(',', LDAP_ATTRS));
				if($sr)
				{
					$records = ldap_get_entries($ldap, $sr);
					foreach($records as $account)
					{
						if(!empty($account['samaccountname'][0]) && !empty($account['givenname'][0]) && !empty($account['sn'][0]))
						{
							/*
							echo @$account['samaccountname'][0];
							echo ' '.@$account['sn'][0];
							echo ' '.@$account['givenname'][0];
							//echo ' '.@$account['name'][0];
							echo ' '.@$account['displayname'][0];
							echo ' '.@$account['mail'][0];
							echo ' '.@$account['telephonenumber'][0];
							echo ' '.@$account['mobile'][0];
							echo ' '.@$account['description'][0];
							echo ' '.@$account['title'][0];
							echo ' '.@$account['department'][0];
							echo ' '.@$account['company'][0];
							echo ' '.@$account['info'][0];
							echo "\n";
							/**/

							print_r($account);

							echo "\n***\n";

						}
					}
					ldap_control_paged_result_response($ldap, $sr, $cookie);
					ldap_free_result($sr);
				}

			}
			while($cookie !== null && $cookie != '');

			ldap_unbind($ldap);
			echo 'Updated: '.$count_updated.', added: '.$count_added.' contacts';
		}
	}
