
function gi(name)
{
	return document.getElementById(name);
}

function escapeHtml(text) {
  return text
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
}

function formatbytes(bytes, decimals) {
   if(bytes == 0) return '0 B';
   var k = 1024;
   var dm = decimals || 2;
   var sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
   var i = Math.floor(Math.log(bytes) / Math.log(k));
   return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
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
  if(typeof XMLHttpRequest === 'undefined')
  {
  		XMLHttpRequest = function()
  		{
  				try { return new ActiveXObject("Msxml2.XMLHTTP.6.0"); } catch(e) {}
  				try { return new ActiveXObject("Msxml2.XMLHTTP.3.0"); } catch(e) {}
  				try { return new ActiveXObject("Msxml2.XMLHTTP"); } catch(e) {}
  				try { return new ActiveXObject("Microsoft.XMLHTTP"); } catch(e) {}
  				return null;
  		};
  }
  return new XMLHttpRequest();
}

function f_http(url, f_callback, callback_params, content_type, data)
{
	if(typeof f_callback === 'undefined') f_callback = null;
	if(typeof callback_params === 'undefined') callback_params = null;
	if(typeof contwnt_type === 'undefined') content_type = null;
	if(typeof data === 'undefined') data = null;
	
	var xhr = f_xhr();
	if(!xhr)
	{	
				if(f_callback)
				{
					f_callback({code: 1, status: "AJAX error: XMLHttpRequest unsupported"}, callback_params);
				}

		return false;
	}
	
		xhr.open(content_type?"post":"get", url, true);
		xhr.onreadystatechange = function(e)
		{
			if(this.readyState == 4)
			{
				var result;
				if(this.status == 200)
				{
					try
					{
						result = JSON.parse(this.responseText);
					}
					catch(e)
					{
						result = {code: 1, status: "Response: "+this.responseText};
					}
				}
				else
				{
					result = {code: 1, status: "AJAX error code: "+this.status};
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
	}
	

	return true;
}