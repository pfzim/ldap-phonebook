<?php
// For Parsec 2.5
function get_acs_location($user_id, $samname, $first_name, $last_name)
/*
	RETURN:
		0 - unknown status
		1 - in office
		2 - out office
*/
{
	$result = 0;
	
	$conn = @odbc_connect("Driver={SQL Server};Address=192.168.0.1,1045;Network=DBMSSOCN;SERVER=192.168.0.1;DATABASE=ParsecDB;LANGUAGE=us_english", "sa", "parsec");
	if($conn)
	{
		//$res = odbc_exec($conn, rpv("SELECT TOP 1 CAST(TranCode AS VARCHAR) FROM dbo.TransLog WHERE ((TranCode = 72) OR (TranCode = 73)) AND (TranUserID = #) ORDER BY TranDateTime DESC", $user_id));
		//$res = odbc_exec($conn, rpv("SELECT TOP 1 CAST(TranCode AS VARCHAR) FROM dbo.TransLog WHERE ((TranCode = 72) OR (TranCode = 73)) AND (TranUserID = (SELECT ID_USER FROM dbo.Personel WHERE FirstName = ! AND SecondName = !)) ORDER BY TranDateTime DESC", $first_name, $last_name));

		// SQL QUERY NOT TESTED!
		$res = odbc_exec($conn, rpv("SELECT TOP 1 CAST(j1.TranCode AS VARCHAR) FROM dbo.Personel m LEFT JOIN dbo.TransLog j1 ON j1.TranUserID = m.ID_USER AND ((j1.TranCode = 72) OR (j1.TranCode = 73)) WHERE m.FirstName = ! AND m.SecondName = ! ORDER BY j1.TranDateTime DESC", $first_name, $last_name));
		if($res)
		{
			$row = odbc_fetch_row($res);
			
			if(intval($row) == 72)
			{
				$result = 1;
			}
			else
			{
				$result = 2;
			}

			odbc_free_result($res);
		}
		odbc_close($conn);
	}

	return $result;
}
