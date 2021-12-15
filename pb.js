var map = 0;
var map_count = 0;

function gi(name)
{
	return document.getElementById(name);
}

function escapeHtml(text)
{
  return (''+text)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
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

function formatbytes(bytes, decimals) {
   if(bytes == 0) return '0 B';
   var k = 1024;
   var dm = decimals || 2;
   var sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
   var i = Math.floor(Math.log(bytes) / Math.log(k));
   return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
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

/*
form_data = {
	code = '0 - success, otherwise error',
	message = 'error message',
	title = 'form name',
	fields = [
		{
			type = 'hidden, list, date, number, string',
			name = 'post name',
			title = 'human readable caption'
			value = 'default value'
			list = [
				'select value 1',
				'list values',
				...
			]
		},
		...
	]
}
*/

function f_append_fields(el, fields, form_id, spoiler_id)
{
	//console.log('f_append_fields' + spoiler_id);
	for(var i = 0, ec = fields.length; i < ec; i++)
	{
		if(fields[i].type == 'hidden')
		{
			html = '<input name="' + escapeHtml(fields[i].name) + '" type="hidden" value="' + escapeHtml(fields[i].value) + '" />';

			var wrapper = document.createElement('div');
			wrapper.innerHTML = html;
			el.appendChild(wrapper);
		}
		else if(fields[i].type == 'list' && fields[i].list)
		{
			html = '<div class="form-title"><label for="' + escapeHtml(form_id + fields[i].name) + '">'+ escapeHtml(fields[i].title) + ':</label></div>'
				+ '<select class="form-field" id="' + escapeHtml(form_id + fields[i].name) + '" name="'+ escapeHtml(fields[i].name) + '">'
				+ '<option value=""></option>';
			for(j = 0; j < fields[i].list.length; j++)
			{
				selected = ''
				if(fields[i].values)
				{
					if(fields[i].values[j] == fields[i].value)
					{
						selected = ' selected="selected"'
					}
				}
				else if(fields[i].list[j] == fields[i].value)
				{
					selected = ' selected="selected"'
				}
				html += '<option value="' + escapeHtml(fields[i].values ? fields[i].values[j] : fields[i].list[j]) + '"' + selected + '>' + escapeHtml(fields[i].list[j]) + '</option>';
			}
			html += '</select>'
				+ '<div id="' + escapeHtml(form_id + fields[i].name) + '-error" class="form-error"></div>';

			var wrapper = document.createElement('div');
			wrapper.innerHTML = html;
			el.appendChild(wrapper);
		}
		else if(fields[i].type == 'flags' && fields[i].list)
		{
			value = parseInt(fields[i].value, 10);

			html = '<div class="form-title">' + escapeHtml(fields[i].title) + ':</div>';
			for(j = 0; j < fields[i].list.length; j++)
			{
				checked = '';
				if(value & (0x01 << j))
				{
					checked = ' checked="checked"';
				}

				html += '<span><input id="' + escapeHtml(form_id + fields[i].name) + '[' + j +']" name="' + escapeHtml(fields[i].name) + '[' + j +']" type="checkbox" value="' + (fields[i].values?fields[i].values[j]:'1') + '"' + checked + '/><label for="'+ escapeHtml(form_id + fields[i].name) + '[' + j + ']">' + escapeHtml(fields[i].list[j]) + '</label></span>'
			}
			html += '<div id="' + escapeHtml(form_id + fields[i].name) + '[0]-error" class="form-error"></div>';

			var wrapper = document.createElement('div');
			wrapper.innerHTML = html;
			el.appendChild(wrapper);
		}
		else if(fields[i].type == 'datetime')
		{
			var wrapper = document.createElement('div');
			wrapper.innerHTML = '<div class="form-title"><label for="' + escapeHtml(form_id + fields[i].name) + '">' + escapeHtml(fields[i].title) + ':</label></div>'
				+ '<input class="form-field" id="'+ escapeHtml(form_id + fields[i].name) + '" name="'+ escapeHtml(fields[i].name) + '" type="edit" value="' + escapeHtml(fields[i].value) + '"/>'
				+ '<div id="'+ escapeHtml(form_id + fields[i].name) + '-error" class="form-error"></div>';
			el.appendChild(wrapper);

			flatpickr(
				gi(form_id + fields[i].name),
				{
					allowInput: true,
					enableTime: true,
					time_24hr: true,
					defaultHour: 0,
					defaultMinute: 0,
					dateFormat: "d.m.Y H:i"
				}
			);
		}
		else if(fields[i].type == 'time')
		{
			var wrapper = document.createElement('div');
			wrapper.innerHTML = '<div class="form-title"><label for="' + escapeHtml(form_id + fields[i].name) + '">' + escapeHtml(fields[i].title) + ':</label></div>'
				+ '<input class="form-field" id="'+ escapeHtml(form_id + fields[i].name) + '" name="'+ escapeHtml(fields[i].name) + '" type="edit" value="' + escapeHtml(fields[i].value) + '"/>'
				+ '<div id="'+ escapeHtml(form_id + fields[i].name) + '-error" class="form-error"></div>';
			el.appendChild(wrapper);

			flatpickr(
				gi(form_id + fields[i].name),
				{
					allowInput: true,
					enableTime: true,
					noCalendar: true,
					time_24hr: true,
					defaultHour: 0,
					defaultMinute: 0,
					dateFormat: "H:i"
				}
			);
		}
		else if(fields[i].type == 'date')
		{
			var wrapper = document.createElement('div');
			wrapper.innerHTML = '<div class="form-title"><label for="' + escapeHtml(form_id + fields[i].name) + '">' + escapeHtml(fields[i].title) + ':</label></div>'
				+ '<input class="form-field" id="'+ escapeHtml(form_id + fields[i].name) + '" name="'+ escapeHtml(fields[i].name) + '" type="edit" value="' + escapeHtml(fields[i].value) + '"/>'
				+ '<div id="'+ escapeHtml(form_id + fields[i].name) + '-error" class="form-error"></div>';
			el.appendChild(wrapper);

			flatpickr(
				gi(form_id + fields[i].name),
				{
					allowInput: true,
					dateFormat: "d.m.Y"
				}
			);
			/*
			var picker = new Pikaday({
				field: gi(form_id + fields[i].name),
				format: 'DD.MM.YYYY'
			});
			*/
		}
		else if(fields[i].type == 'password')
		{
			html = '<div class="form-title"><label for="'+ escapeHtml(form_id + fields[i].name) + '">' + escapeHtml(fields[i].title) + ':</label></div>'
				+ '<input class="form-field" id="' + escapeHtml(form_id + fields[i].name) + '" name="'+ escapeHtml(fields[i].name) + '" type="password" value=""/>'
				+ '<div id="'+ escapeHtml(form_id + fields[i].name) + '-error" class="form-error"></div>';

			var wrapper = document.createElement('div');
			wrapper.innerHTML = html;
			el.appendChild(wrapper);
		}
		else if(fields[i].type == 'upload')
		{
			html = '<div class="form-title"><label for="'+ escapeHtml(form_id + fields[i].name) + '">' + escapeHtml(fields[i].title) + ':</label></div>'
				+ '<span class="form-upload" id="' + escapeHtml(form_id + fields[i].name) + '-file">&nbsp;</span> <a href="#" onclick="gi(\'' + escapeHtml(form_id + fields[i].name) + '\').click(); return false;"/>' + LL.SelectFile + '</a>'
				+ '<input id="' + escapeHtml(form_id + fields[i].name) + '" name="'+ escapeHtml(fields[i].name) + '" type="file" style="display: none"/>'
				+ '<div id="' + escapeHtml(form_id + fields[i].name) + '-error" class="form-error"></div>';

			var wrapper = document.createElement('div');
			wrapper.innerHTML = html;
			el.appendChild(wrapper);

			gi(form_id + fields[i].name).onchange = function(name) {
				return function() {
					gi(name + '-file').textContent = this.files.item(0).name;
				}
			}(form_id + fields[i].name);
		}
		else if(fields[i].type == 'spoiler')
		{
			spoiler_id++;

			var wrapper = document.createElement('div');
			wrapper.setAttribute('onclick', 'f_toggle_spoiler(\'' + escapeHtml(form_id + '_spoiler_' + spoiler_id) + '\');');
			wrapper.className = 'spoiler';
			wrapper.textContent = fields[i].title;
			el.appendChild(wrapper);

			wrapper = document.createElement('div');
			wrapper.id = form_id + '_spoiler_' + spoiler_id;
			wrapper.style.display = 'none';
			el.appendChild(wrapper);

			spoiler_id = f_append_fields(wrapper, fields[i].fields, form_id, spoiler_id);
		}
		else
		{
			var placeholder = '';
			if(fields[i].placeholder)
			{
				placeholder = '" placeholder="' + fields[i].placeholder;
			}

			html = '<div class="form-title"><label for="'+ escapeHtml(form_id + fields[i].name) + '">' + escapeHtml(fields[i].title) + ':</label></div>'
				+ '<input class="form-field" id="' + escapeHtml(form_id + fields[i].name) + '" name="'+ escapeHtml(fields[i].name) + '" type="edit" value="'+ escapeHtml(fields[i].value) + placeholder + '"/>'
				+ '<div id="'+ escapeHtml(form_id + fields[i].name) + '-error" class="form-error"></div>';

			var wrapper = document.createElement('div');
			wrapper.innerHTML = html;
			el.appendChild(wrapper);
			
			if(fields[i].autocomplete)
			{
				autocomplete_create(gi(form_id + fields[i].name), fields[i].autocomplete);
			}
		}
	}

	return spoiler_id;
}

function on_received_form(data, form_id)
{
	gi('loading').style.display = 'none';
	if(data.code)
	{
		f_notify(data.message, 'error');
	}
	else
	{
		gi(form_id + '-title').innerText = data.title;

		var el = gi(form_id + '-description');
		if(data.description && (data.description.length > 0))
		{
			el.innerText = data.description;
			el.style.display = 'block';
		}
		else
		{
			el.innerText = '';
			el.style.display = 'none';
		}

		el = gi(form_id + '-fields');
		el.innerHTML = '';
		html = '';
		
		f_append_fields(el, data.fields, form_id, 0);
		
		html = '<br /><div class="f-right">'
			+ '<button class="button-accept" type="submit" onclick="return f_send_form(\'' + data.action + '\');">' + LL.OK + '</button>'
			+ '&nbsp;'
			+ '<button class="button-decline" type="button" onclick="this.parentNode.parentNode.parentNode.parentNode.parentNode.style.display=\'none\'">' + LL.Cancel + '</button>'
			+ '</div>';

		var wrapper = document.createElement('div');
		wrapper.innerHTML = html;
		el.appendChild(wrapper);

		gi(form_id +'-container').style.display='block';
	}
}

function f_show_form(url)
{
	var form_id = 'uform';
	gi('loading').style.display = 'block';
	f_http(
		url,
		on_received_form,
		form_id
	);

	return false;
}

function f_send_form(action)
{
	var form_id = 'uform';
	var form_data = {};
	var el = gi(form_id + '-fields');
	for(i = 0; i < el.elements.length; i++)
	{
		if(el.elements[i].name)
		{
			var err = gi(form_id + el.elements[i].name + '-error');
			if(err)
			{
				err.style.display = 'none';
			}

			/*
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
			*/
		}
	}

	//alert(json2url(form_data));
	//return;

	gi('loading').style.display = 'block';
	f_http(
		g_link_prefix + action,
		function(data, form_id)
		{
			gi('loading').style.display = 'none';
			f_notify(data.message, data.code?"error":"success");
			if(!data.code)
			{
				gi(form_id+'-container').style.display='none';
				//window.location = '?action=doc&id='+data.id;
				//window.location = window.location;
				//f_update_doc(data.data);
				//f_get_perms();
				on_saved(action, data);
			}
			else if(data.errors)
			{
				for(i = 0; i < data.errors.length; i++)
				{
					var el = gi(form_id + data.errors[i].name + '-error');
					if(el)
					{
						el.textContent = data.errors[i].msg;
						el.style.display = 'block';
					}
				}
			}
		},
		form_id,
		null,                                    //'application/x-www-form-urlencoded',
		new FormData(gi(form_id + '-fields'))    //json2url(form_data)
	);

	return false;
}

function f_notify(text, type)
{
	var el;
	var temp;
	el = gi('notify-block');
	if(!el)
	{
		temp = document.getElementsByTagName('body')[0];
		el = document.createElement('div');
		el.id = 'notify-block';
		el.style.top = '0px';
		el.style.right = '0px';
		el.className = 'notifyjs-corner';
		temp.appendChild(el);
	}

	temp = document.createElement('div');
	temp.innerHTML = '<div class="notifyjs-wrapper notifyjs-hidable"><div class="notifyjs-arrow"></div><div class="notifyjs-container" style=""><div class="notifyjs-bootstrap-base notifyjs-bootstrap-'+escapeHtml(type)+'"><span data-notify-text="">'+escapeHtml(text)+'</span></div>';
	temp = el.appendChild(temp.firstChild);

	setTimeout(
		(function(el)
		{
			return function() {
				el.parentNode.removeChild(el);
			};
		})(temp),
		5000
	);
}

function f_msg(text)
{
	gi('message-text').innerText = text;
	gi('message-box').style.display = 'block';
	return false;
}

function on_saved(action, data)
{
	if(action == 'permission_save')
	{
		//f_get_perms(data.pid);
		window.location = window.location;
	}
	else if(action == 'user_save')
	{
		window.location = window.location;
	}
	else if(action == 'contact_save')
	{
		window.location = window.location;
	}
	else if(action == 'register')
	{
		f_msg(data.message);
	}
}

function f_sw_img(ev)
{
	var el_src = ev.target || ev.srcElement;
	var img = el_src.parentNode.getAttribute('data-flags');
	if(parseInt(img, 10) & 0x0008)
	{
		var el = gi('userphoto');
		el.src = 'photos/t'+el_src.parentNode.getAttribute('data-id')+'.jpg';
		el = gi('imgblock');
		el.style.display = 'block';
		el.style.left = (ev.clientX+10)  + "px";
		el.style.top = (ev.clientY+10)  + "px";
	}
}

function f_mv_img(ev)
{
	var el = gi('imgblock');
	if(el)
	{
		el.style.left = (ev.clientX+10)  + "px";
		el.style.top = (ev.clientY+10)  + "px";
	}
}

function f_sw_map(ev)
{
	var el_src = ev.target || ev.srcElement;
	var id = parseInt(el_src.parentNode.getAttribute('data-map'), 10);
	if(id)
	{
		var el = gi('map-container');
		var x = parseInt(el_src.parentNode.getAttribute('data-x'), 10);
		var y = parseInt(el_src.parentNode.getAttribute('data-y'), 10);
		el.style.display = 'block';
		el.onclick = function() {gi('map-container').style.display = 'none';};
		var map = gi('map-image');
		map.src = '';
		map.onload = function(x, y)
		{
			return function(ev)
			{
				var el = gi('map-marker');
				var el_src = gi('map-image');
				if(el)
				{
					el.onclick = null;
					el.style.display = 'block';
					el.style.left = (el_src.offsetLeft + x - el.width/2)  + "px";
					el.style.top = (el_src.offsetTop + y - el.height/2)  + "px";
					//alert("    x: "+(el_src.offsetLeft + x) +"    y: "+(el_src.offsetTop + y));
				}
			}
		}(x, y);
		map.src = g_link_static_prefix + 'templates/map' + id + '.png';
	}
}

function f_set_location(id, map, x, y)
{
	//alert("map: "+map+"    x: "+x+"    y: "+y);
	f_http(
		g_link_prefix + 'contact_location_set',
		function(data, params)
		{
			f_notify(data.message, data.code?"error":"success");
			if(!data.code)
			{
				var row = gi('row'+data.id);
				if(row)
				{
					row.setAttribute("data-map", data.map);
					row.setAttribute("data-x", data.x);
					row.setAttribute("data-y", data.y);
				}
			}
		},
		null,
		'application/x-www-form-urlencoded',
		json2url({'id' : id, 'map': map, 'x': x, 'y': y })
	);
}

function f_map_set(ev)
{
	var el_src = ev.target || ev.srcElement;
	var id = el_src.parentNode.parentNode.parentNode.getAttribute('data-id');
	var map = el_src.getAttribute('data-map');
	gi('map-container').onclick = null;
	gi('map-image').onload = null;
	gi('map-image').src = g_link_static_prefix + 'templates/map'+map+'.png';
	gi('map-container').style.display='block';
	gi('map-marker').style.display='none';
	gi('map-image').onclick = function(event)
	{
		gi('map-marker').style.display='block';
		gi('map-marker').style.left = (event.clientX - gi('map-marker').width/2)  + "px";
		gi('map-marker').style.top = (event.clientY - gi('map-marker').height/2)  + "px";
		gi('map-marker').onclick = function()
		{
			f_set_location(id, map, (gi('map-marker').offsetLeft + gi('map-marker').width/2) - gi('map-image').offsetLeft, (gi('map-marker').offsetTop + gi('map-marker').height/2) - gi('map-image').offsetTop);
			gi('map-container').style.display='none';
			gi('map-image').onclick = null;
		};
	};
};

function f_map_unset(ev)
{
	var el_src = ev.target || ev.srcElement;
	var id = el_src.parentNode.parentNode.parentNode.getAttribute('data-id');
	f_set_location(id, 0, 0, 0);
}

function f_hide(ev)
{
	var el_src = ev.target || ev.srcElement;
	var id = el_src.parentNode.parentNode.parentNode.getAttribute('data-id');
	f_http(
		g_link_prefix + 'contact_hide',
		function(data, el)
		{
			f_notify(data.message, data.code?"error":"success");
			if(!data.code)
			{
				el.textContent = 'Show';
				el.onclick = function(event) { f_show(event); };
			}
		},
		el_src,
		'application/x-www-form-urlencoded',
		json2url({'id': id })
	);
};

function f_hide2(ev)
{
	var el_src = ev.target || ev.srcElement;
	var id = el_src.parentNode.parentNode.parentNode.getAttribute('data-id');
	f_http(
		g_link_prefix + 'contact_hide',
		function(data, el)
		{
			f_notify(data.message, data.code?"error":"success");
			if(!data.code)
			{
				gi('menu-cmd-hide').style.display = 'none';
				gi('menu-cmd-show').style.display = 'block';
			}
		},
		el_src,
		'application/x-www-form-urlencoded',
		json2url({'id': id })
	);
};

function f_show(ev)
{
	var el_src = ev.target || ev.srcElement;
	var id = el_src.parentNode.parentNode.parentNode.getAttribute('data-id');
	f_http(
		g_link_prefix + 'contact_show',
		function(data, el)
		{
			f_notify(data.message, data.code?"error":"success");
			if(!data.code)
			{
				el.textContent = 'Hide';
				el.onclick = function(event) { f_hide(event); };
			}
		},
		el_src,
		'application/x-www-form-urlencoded',
		json2url({'id': id })
	);
};

function f_show2(ev)
{
	var el_src = ev.target || ev.srcElement;
	var id = el_src.parentNode.parentNode.parentNode.getAttribute('data-id');
	f_http(
		g_link_prefix + 'contact_show',
		function(data, el)
		{
			f_notify(data.message, data.code?"error":"success");
			if(!data.code)
			{
				gi('menu-cmd-hide').style.display = 'block';
				gi('menu-cmd-show').style.display = 'none';
			}
		},
		el_src,
		'application/x-www-form-urlencoded',
		json2url({'id': id })
	);
};

function f_get_acs_location(ev)
{
	var el_src = ev.target || ev.srcElement;
	var id = el_src.parentNode.parentNode.parentNode.getAttribute('data-id');
	f_http(
		g_link_prefix + 'get_acs_location/' + id,
		function(data, el)
		{
			if(!data.code)
			{
				var temp_str = 'unknown status';
				switch(data.location)
				{
					case 1:
						temp_str = 'In office';
						break;
					case 2:
						temp_str = 'Out office';
						break;
				}
				f_notify(temp_str, data.location?"success":"error");
			}
			else
			{
				f_notify(data.message, "error");
			}
		},
		el_src
	);
};

function f_delete(ev)
{
	var el_src = ev.target || ev.srcElement;
	var id = el_src.parentNode.parentNode.parentNode.getAttribute('data-id');
	f_http(
		g_link_prefix + 'contact_delete',
		function(data, id)
		{
			f_notify(data.message, data.code?"error":"success");
			if(!data.code)
			{
				var row = gi('row' + id);
				row.parentNode.removeChild(row);
				gi('contact-menu').style.display = 'none';
			}
		},
		id,
		'application/x-www-form-urlencoded',
		json2url({'id': id })
	);
};

function f_delete_photo(ev)
{
	var el_src = ev.target || ev.srcElement;
	var id = el_src.parentNode.parentNode.parentNode.getAttribute('data-id');
	f_http(
		g_link_prefix + 'contact_photo_delete',
		function(data, el)
		{
			f_notify(data.message, data.code?"error":"success");
			if(!data.code)
			{
				gi('contact-menu').style.display = 'none';
				//var row = el.parentNode.parentNode;
				//row.parentNode.removeChild(row);
			}
		},
		el_src,
		'application/x-www-form-urlencoded',
		json2url({'id': id })
	);
};

function on_action_success(el, action, data)
{
	if(action == 'user_deactivate')
	{
		window.location = window.location;
	}
	else if(action == 'user_activate')
	{
		window.location = window.location;
	}
	else
	{
		var row = el.parentNode.parentNode;
		row.parentNode.removeChild(row);
	}
}

function f_call_action(ev, action)
{
	gi('loading').style.display = 'block';
	var el_src = ev.target || ev.srcElement;
	var id = el_src.parentNode.parentNode.getAttribute('data-id');
	f_http(
		g_link_prefix + action,
		function(data, params)
		{
			gi('loading').style.display = 'none';
			f_notify(data.message, data.code?"error":"success");
			if(!data.code)
			{
				on_action_success(params.el, params.action, data);
			}
		},
		{el: el_src, action: action},
		'application/x-www-form-urlencoded',
		json2url({id: id})
	);
}

function f_delete_perm(ev)
{
	if(window.confirm(LL.ConfirmDelete))
	{
		f_call_action(ev, 'permission_delete');
	}

	return false;
}

function f_deactivate_user(ev)
{
	f_call_action(ev, 'user_deactivate');
}

function f_delete_user(ev)
{
	if(window.confirm(LL.ConfirmDelete))
	{
		f_call_action(ev, 'user_delete');
	}
}

function f_activate_user(ev)
{
	f_call_action(ev, 'user_activate');
}

function f_confirm_async(a)
{
	if(window.confirm(LL.ConfirmOperation))
	{
		return f_async_ex(a.href);
	}

	return false;
}

function f_async(a)
{
	return f_async_ex(a.href);
}

function f_async_ex(url)
{
	gi('loading').style.display = 'block';
	f_http(
		url,
		function(data, el)
		{
			gi('loading').style.display = 'none';
			f_notify(data.message, data.code?"error":"success");
			f_msg(data.message);
		},
		null
	);

	return false;
}

function f_search(f)
{
    //f.action = f.action + '/' + encodeURIComponent(gi('search').value);
    //f.submit();

	window.location = f.action + '/' + encodeURIComponent(gi('search').value);

	return false;
}

function f_update_row(id)
{
	f_http(
		g_link_prefix + 'contact_get/' + id,
		function(data, params)
		{
			if(data.code)
			{
				f_notify(data.message, "error");
			}
			else
			{	
				// Ищем строку по ID
				var row = gi('row'+data.data.id);
				// Если строка не найдена - считаем ее новой
				if(!row)
				{	
					// Добавляем новую строку в таблицу
					row = gi("table-data").insertRow(0);
					row.insertCell(0);
					row.insertCell(1);
					row.insertCell(2);
					row.insertCell(3);
					row.insertCell(4);
					row.insertCell(5);
					row.insertCell(6);
					row.insertCell(7);
					row.insertCell(8);
					// Заполняем атрибут id у ячеек строки
					row.cells[1].setAttribute('id', "nameCell"+data.data.id);				// ФИО
					row.cells[2].setAttribute('id', "pintCell"+data.data.id);				// Внутренний телефон
					row.cells[3].setAttribute('id', "pcityCell"+data.data.id);				// Городской телефон
					row.cells[4].setAttribute('id', "pcellCell"+data.data.id);				// Мобильный телефон
					row.cells[5].setAttribute('id', "mailCell"+data.data.id);				// Электронная почта
					row.cells[6].setAttribute('id', "posCell"+data.data.id);				// Должность
					row.cells[7].setAttribute('id', "depCell"+data.data.id);				// Подразделение
					row.cells[8].setAttribute('id', "mainMenuCell"+data.data.id);			// Меню
				}

				row.id = 'row'+data.data.id;
				row.setAttribute("data-id", 	data.data.id);
				row.setAttribute("data-map", 	data.data.map);
				row.setAttribute("data-x", 		data.data.x);
				row.setAttribute("data-y", 		data.data.y);
				row.setAttribute("data-flags", 	data.data.flags);
				
				// Получаем ячейки таблицы по ID
				var nameCell 				= gi("nameCell"+data.data.id);					// ФИО
				var phoneCell 				= gi("pintCell"+data.data.id);					// Внутренний телефон
				var phoneCityCell 			= gi("pcityCell"+data.data.id);					// Городской телефон
				var mobileCell 				= gi("pcellCell"+data.data.id);					// Мобильный телефон
				var mailCell 				= gi("mailCell"+data.data.id);					// Электронная почта
				var positionCell 			= gi("posCell"+data.data.id);					// Должность
				var departmentCell 			= gi("depCell"+data.data.id);					// Подразделение
				var menuCell 				= gi("mainMenuCell"+data.data.id);				// Меню
				
				// Заполняем найденные ячейки данными
				nameCell.textContent 		= data.data.last_name + ' ' + data.data.first_name + ' ' + data.data.middle_name;
				if(data.data.photo) {
					nameCell.className = 'userwithphoto';
				}
				nameCell.style.cursor 		= 'pointer';
				nameCell.onclick 			= function(event) { f_sw_map(event); };
				nameCell.onmouseenter 		= function(event) { f_sw_img(event); };
				nameCell.onmouseleave 		= function(event) { gi('imgblock').style.display = 'none'; };
				nameCell.onmousemove 		= function(event) { f_mv_img(event); };
				
				phoneCell.textContent 		= data.data.phone_internal;				
				phoneCityCell.textContent 	= data.data.phone_external;
				mobileCell.textContent 		= data.data.phone_mobile;
				mailCell.innerHTML 			= '<a href="mailto:'+escapeHtml(data.data.mail)+'">'+escapeHtml(data.data.mail)+'</a>';
				positionCell.textContent 	= data.data.position;
				departmentCell.textContent 	= data.data.department;
				menuCell.innerHTML 			= '<span class="command" onclick="f_menu(event);">Menu</span>';
			}
		}
	);
}

function f_edit(ev)
{
	var el_src = ev.target || ev.srcElement;
	var id = el_src.parentNode.parentNode.parentNode.getAttribute('data-id');
	f_show_form(g_link_prefix + 'contact_edit/' + id);
}

function f_upload_photo(id)
{
	gi('loading').style.display = 'block';
	var fd = new FormData(gi("form-file-upload"));
	fd.append('id', id);
	f_http(
		g_link_prefix + 'contact_photo_set',
		function(data, params)
		{
			gi('loading').style.display = 'none';
			f_notify(data.message, data.code?"error":"success");
			if(!data.code)
			{
				f_update_row(data.id);
			}
		},
		null,
		null,
		fd
	);

	return false;
}

function f_upload_file(action)
{
	gi('loading').style.display = 'block';
	var fd = new FormData(gi("form-file-upload"));
	f_http(
		g_link_prefix + action,
		function(data, params)
		{
			gi('loading').style.display = 'none';
			f_notify(data.message, data.code?"error":"success");
		},
		null,
		null,
		fd
	);

	return false;
}

function f_photo(ev)
{
	var id = 0;
	if(ev)
	{
		var el_src = ev.target || ev.srcElement;
		id = el_src.parentNode.parentNode.parentNode.getAttribute('data-id');
	}
	if(id)
	{
		gi('file-upload').onchange = function(id) {
			return function() {
				f_upload_photo(id);
			}
		}(id);
		gi('file-upload').click();
	}
}

var parentElement;

documentClick = function (event) {
	var parent;
	var wrapperElement = gi('contact-menu');
	if (event.target !== parentElement && event.target !== wrapperElement) {
		parent = event.target.parentNode;
		  while (parent !== wrapperElement && parent !== parentElement) {
			  parent = parent.parentNode;
			  if (parent === null) {
				wrapperElement.style.display = 'none';
				//wrapperElement.parentNode.removeChild(wrapperElement);
				document.removeEventListener('click', documentClick, false);
				wrapperElement = null;
				  break;
			  }
		}
	}
};

function f_menu(ev)
{
	var id = 0;
	var el_src;
	if(ev)
	{
		el_src = ev.target || ev.srcElement;
		id = el_src.parentNode.parentNode.getAttribute('data-id');
		f_menu_id(ev, el_src, id);
	}
}

function f_menu_id(ev, el_src, id)
{
	if(id)
	{
		var el = gi('contact-menu');
		var pX = ev.pageX || (ev.clientX + (document.documentElement && document.documentElement.scrollLeft || document.body && document.body.scrollLeft || 0) - (document.documentElement.clientLeft || 0));
		var pY = ev.pageY || (ev.clientY + (document.documentElement && document.documentElement.scrollTop || document.body && document.body.scrollTop || 0) - (document.documentElement.clientTop || 0));
		pX = Math.round(pX-190);
		pY = Math.round(pY+5);
		if(pX < 0) pX = 0;
		if(pY < 0) pY = 0;
		el.style.left = pX  + "px";
		el.style.top = pY + "px";
		el.setAttribute('data-id', id);
		gi('menu-cmd-edit').style.display = 'none';
		gi('menu-cmd-photo').style.display = 'none';
		gi('menu-cmd-delete').style.display = 'none';
		gi('menu-cmd-delete-photo').style.display = 'none';
		gi('menu-cmd-show').style.display = 'none';
		gi('menu-cmd-hide').style.display = 'none';
		gi('menu-cmd-connect-0').style.display = 'none';
		gi('menu-cmd-connect-1').style.display = 'none';
		gi('menu-cmd-connect-2').style.display = 'none';
		gi('menu-loading').style.display = 'block';
		el.style.display = 'block';
		parentElement = el_src;
		document.addEventListener('click', documentClick, false);
		
		f_http(
			g_link_prefix + 'contact_get/' + id,
			function(data, el)
			{
				gi('menu-loading').style.display = 'none';
				// add pc to list
				if(data.code)
				{
					f_notify(data.message, "error");
				}
				else
				{
					if(data.data.adid == '')
					{
						gi('menu-cmd-edit').style.display = 'block';
						gi('menu-cmd-delete').style.display = 'block';
						if(data.data.flags & 0x0008)
						{
							gi('menu-cmd-delete-photo').style.display = 'block';
						}
						else
						{
							gi('menu-cmd-photo').style.display = 'block';
						}
					}
					if(data.data.flags & 0x0001)
					{
						gi('menu-cmd-hide').style.display = 'block';
					}
					else
					{
						gi('menu-cmd-show').style.display = 'block';
					}
					var i;
					for(i = 0; i < 3; i++)
					{
						if(data.computers[i] != '')
						{
							gi('menu-cmd-connect-'+i).href = 'msraurl:' + data.computers[i];
							gi('menu-cmd-connect-'+i).style.display = 'block';
							gi('menu-cmd-connect-'+i).textContent = 'Connect to ' + data.computers[i];
						}
					}
				}
			},
			el_src
		);
	}
}

function f_import_xml()
{
	gi('file-upload').onchange = function() {
		return function() {
			f_upload_file('contacts_import_xml');
		}
	}();
	gi('file-upload').click();
}

function f_select_all(ev)
{
	var el_src = ev.target || ev.srcElement;
	checkboxes = document.getElementsByName('check');
	for(var i = 0, n = checkboxes.length; i < n; i++)
	{
		checkboxes[i].checked = el_src.checked;
	}
}

function f_export_selected(ev)
{
	var el;
	var postdata = "";
	var j = 0;
	var checkboxes = document.getElementsByName('check');
	for(var i = 0, n = checkboxes.length; i < n;i++)
	{
		if(checkboxes[i].checked)
		{
			if(j > 0)
			{
				postdata += ",";
			}
			postdata += checkboxes[i].value;
			j++;
		}
	}

	if(j > 0)
	{
		el = gi('list');
		el.value = postdata;
		el = gi('contacts');
		el.submit();
	}

	return false;
}

function f_hide_selected(ev)
{
	var postdata = "list=";
	var j = 0;
	var checkboxes = document.getElementsByName('check');
	for(var i = 0, n = checkboxes.length; i < n;i++)
	{
		if(checkboxes[i].checked)
		{
			if(j > 0)
			{
				postdata += ",";
			}
			postdata += checkboxes[i].value;
			j++;
		}
	}
	if(j > 0)
	{
		f_http(
			g_link_prefix + 'contacts_hide_selected',
			function(data, params)
			{
				f_notify(data.message, data.code?"error":"success");
			},
			null,
			'application/x-www-form-urlencoded',
			postdata
		);
	}
	else
	{
		f_popup("Error", "No selection");
	}
	return false;
}

function si(ev)
{
	var el_src = ev.target || ev.srcElement;
	var pX = ev.pageX || (ev.clientX + (document.documentElement && document.documentElement.scrollLeft || document.body && document.body.scrollLeft || 0) - (document.documentElement.clientLeft || 0));
	var pY = ev.pageY || (ev.clientY + (document.documentElement && document.documentElement.scrollTop || document.body && document.body.scrollTop || 0) - (document.documentElement.clientTop || 0));
	document.getElementById('popup').style.display = 'block';
	document.getElementById('popup').style.left = (pX+10)  + "px";
	document.getElementById('popup').style.top = (pY+10)  + "px";
	if(parseInt(el_src.getAttribute('data-flags'), 10) & 0x0008)
	{
		document.getElementById('u_photo').src = g_link_static_prefix + 'photos/t'+el_src.getAttribute('data-id')+'.jpg';
	}
	else
	{
		document.getElementById('u_photo').src = g_link_static_prefix + 'templates/nophoto.png';
	}
	document.getElementById('u_name').innerHTML = escapeHtml(el_src.getAttribute('data-name'));
	document.getElementById('u_position').innerHTML = escapeHtml(el_src.getAttribute('data-position'));
	document.getElementById('u_phone').innerHTML = escapeHtml(el_src.getAttribute('data-phone'));
}

function mi(ev)
{
	var el = document.getElementById('popup');
	if(el)
	{
		var pX = ev.pageX || (ev.clientX + (document.documentElement && document.documentElement.scrollLeft || document.body && document.body.scrollLeft || 0) - (document.documentElement.clientLeft || 0));
		var pY = ev.pageY || (ev.clientY + (document.documentElement && document.documentElement.scrollTop || document.body && document.body.scrollTop || 0) - (document.documentElement.clientTop || 0));
		el.style.left = (pX+10)  + "px";
		el.style.top = (pY+10)  + "px";
	}
}

/* old function click-click-move
function f_click(ev)
{
	el_src.style.border="1px dashed red";
	el_src.style.borderRadius = "5px";
	var el = document.getElementById('map-image');
	el.onclick = function(id) {
		return function(event)
		{
			var box = document.getElementById('map-image').getBoundingClientRect()
			//alert('px: '+event.pageX+'  py: '+event.pageY+'   cx: '+(box.left)+'  py: '+(box.top));
			f_set_location(id, <?php eh($id);?>, event.pageX - box.left - window.scrollX, event.pageY - box.top - window.scrollY);
			document.getElementById('u'+id).style.border="0px dashed black";
			document.getElementById('u'+id).style.left = (event.pageX - box.left - window.scrollX - 16)+'px';
			document.getElementById('u'+id).style.top = (event.pageY - box.top - window.scrollY - 22)+'px';
			document.getElementById('map-image').onclick = null;
		}
	}(el_src.getAttribute('data-id'));
}
*/

function f_drag(ev)
{
	var el_src = ev.target || ev.srcElement;
	if(ev.button > 1)
	{
		f_menu_id(ev, el_src, el_src.getAttribute('data-id'));
		return false;
	}
	el_src.style.border="1px dashed red";
	el_src.style.borderRadius = "5px";

	var box = document.getElementById('map-image-drag').getBoundingClientRect();
	var sx = (window.pageXOffset !== undefined)? window.pageXOffset: (document.documentElement || document.body.parentNode || document.body).scrollLeft;
	var sy = (window.pageYOffset !== undefined)? window.pageYOffset: (document.documentElement || document.body.parentNode || document.body).scrollTop;
	var pX = ev.pageX || (ev.clientX + (document.documentElement && document.documentElement.scrollLeft || document.body && document.body.scrollLeft || 0) - (document.documentElement.clientLeft || 0));
	var pY = ev.pageY || (ev.clientY + (document.documentElement && document.documentElement.scrollTop || document.body && document.body.scrollTop || 0) - (document.documentElement.clientTop || 0));
	el_src.style.left = Math.round(pX - box.left - sx - 17)+'px';
	el_src.style.top = Math.round(pY - box.top - sy - 23)+'px';

	document.onmousemove = function(id)
	{
		return function(ev)
		{
			var box = document.getElementById('map-image-drag').getBoundingClientRect();
			var sx = (window.pageXOffset !== undefined)? window.pageXOffset: (document.documentElement || document.body.parentNode || document.body).scrollLeft;
			var sy = (window.pageYOffset !== undefined)? window.pageYOffset: (document.documentElement || document.body.parentNode || document.body).scrollTop;
			var pX = ev.pageX || (ev.clientX + (document.documentElement && document.documentElement.scrollLeft || document.body && document.body.scrollLeft || 0) - (document.documentElement.clientLeft || 0));
			var pY = ev.pageY || (ev.clientY + (document.documentElement && document.documentElement.scrollTop || document.body && document.body.scrollTop || 0) - (document.documentElement.clientTop || 0));
			var x = Math.round(pX - box.left - sx);
			var y = Math.round(pY - box.top - sy);
			if(x < 0) x = 0;
			if(y < 0) y = 0;
			if(x > box.right - box.left) x = box.right - box.left;
			if(y > box.bottom - box.top) y = box.bottom - box.top;
			//console.log("onmousemove "+id+"    sx "+sy+"     sY "+window.scrollY);
			document.getElementById('u'+id).style.left = (x - 17)+'px';
			document.getElementById('u'+id).style.top = (y - 23)+'px';
		}
	}(el_src.getAttribute('data-id'));

	el_src.onmouseup = function(ev) { f_drop(ev) };
}

function f_drop(ev)
{
	var el_src = ev.target || ev.srcElement;
	document.onmousemove = null;
	var box = document.getElementById('map-image-drag').getBoundingClientRect();
	//alert('px: '+ev.pageX+'  py: '+ev.pageY+'   cx: '+(box.left)+'  py: '+(box.top));
	var sx = (window.pageXOffset !== undefined)? window.pageXOffset: (document.documentElement || document.body.parentNode || document.body).scrollLeft;
	var sy = (window.pageYOffset !== undefined)? window.pageYOffset: (document.documentElement || document.body.parentNode || document.body).scrollTop;
	var pX = ev.pageX || (ev.clientX + (document.documentElement && document.documentElement.scrollLeft || document.body && document.body.scrollLeft || 0) - (document.documentElement.clientLeft || 0));
	var pY = ev.pageY || (ev.clientY + (document.documentElement && document.documentElement.scrollTop || document.body && document.body.scrollTop || 0) - (document.documentElement.clientTop || 0));
	var x = Math.round(pX - box.left - sx);
	var y = Math.round(pY - box.top - sy);
	if(x < 0) x = 0;
	if(y < 0) y = 0;
	if(x > box.right - box.left) x = box.right - box.left;
	if(y > box.bottom - box.top) y = box.bottom - box.top;
	el_src.style.left = (x - 16)+'px';
	el_src.style.top = (y - 22)+'px';
	f_set_location(el_src.getAttribute('data-id'), map, x, y);
	el_src.style.border="0px dashed black";
	el_src.onmouseup = null;
}

function filter_table() {
  // Declare variables
  var input, filter, table, tr, td, i;
  input = gi("search");
  filter = input.value.toLowerCase();
  table = gi("table-data");
  tr = table.getElementsByTagName("tr");

  // Loop through all table rows, and hide those who don't match the search query
  for (i = 0; i < tr.length; i++) {
    tds = tr[i].getElementsByTagName("td");
	var sh = "none";
	var j;
	for(j = 0; j < tds.length; j++)
	{
		if(tds[j])
		{
		  var str = tds[j].textContent || tds[j].innerHTML;
		  if(str.toLowerCase().indexOf(filter) > -1)
		  {
			sh = "";
			break;
		  }
		}
	}
	tr[i].style.display = sh;
  }
}

function autocomplete_on_click(id, value)
{
	gi(id).value = value;
	autocomplete_destroy();
}

function autocomplete_on_input(ev)
{
	var el = ev.target || ev.srcElement;

	if(!el.value )
	{
		return false;
	}

	action = el.getAttribute('data-action');

	f_http(
		g_link_prefix + action,
		function(data, el)
		{
			gi('loading').style.display = 'none';
			if(data.code)
			{
				f_notify(data.message, 'error');
			}
			else
			{
				var a, b, i;
				autocomplete_current_focus = -1;

				autocomplete_destroy(null);
				a = document.createElement('DIV');
				a.setAttribute('id', 'autocomplete-container');
				a.setAttribute('class', 'autocomplete-items');

				el.parentNode.appendChild(a);
				for(i = 0; i < data.list.length; i++)
				{
					b = document.createElement('DIV');
					b.innerHTML = (''+data.list[i]).replace(new RegExp('(' + el.value + ')', 'i'), '<strong>$1</strong>');
					b.setAttribute('onclick', 'autocomplete_on_click(\'' + el.id + '\', \'' + data.list[i] + '\');');
					a.appendChild(b);
				}
			}
		},
		el,
		'application/x-www-form-urlencoded',
		json2url({search: el.value})
	);
}

function autocomplete_on_keydown(e)
{
	var el = e.target || e.srcElement;
	var x = gi('autocomplete-container');
	if(!x)
	{
		return;
	}

	items = x.getElementsByTagName('div');

	if(e.keyCode == 40)
	{
		autocomplete_current_focus++;
		autocomplete_add_active(items);
	}
	else if(e.keyCode == 38) //up
	{
		autocomplete_current_focus--;
		autocomplete_add_active(items);
	}
	else if (e.keyCode == 13)
	{
		e.preventDefault();
		if (autocomplete_current_focus > -1)
		{
			if(items)
			{
				items[autocomplete_current_focus].click();
			}
		}
	}
}

function autocomplete_create(input, action)
{
	input.setAttribute('data-action', action);
	//input.setAttribute('autocomplete', 'off');
	input.addEventListener('input', autocomplete_on_input);
	input.addEventListener('keydown', autocomplete_on_keydown);
}

function autocomplete_add_active(items)
{
	if(!items) return false;

	autocomplete_remove_active(items);

	if(autocomplete_current_focus >= items.length)
	{
		autocomplete_current_focus = 0;
	}

	if(autocomplete_current_focus < 0)
	{
		autocomplete_current_focus = (items.length - 1);
	}

	items[autocomplete_current_focus].classList.add('autocomplete-active');
}

function autocomplete_remove_active(items)
{
	for(var i = 0; i < items.length; i++)
	{
		items[i].classList.remove('autocomplete-active');
	}
}

function autocomplete_destroy(e)
{
	var el = gi('autocomplete-container');
	if(el)
	{
		el.parentNode.removeChild(el);
	}
}

// https://github.com/jfriend00/docReady
// https://github.com/dmilisic/docReady
(function() {
    "use strict";
    var readyFired = false;

    // call this when the document is ready
    // this function protects itself against being called more than once
    function docReady() {
        if (!readyFired) {
            // this must be set to true before we start calling callbacks
            readyFired = true;
            // TODO: Enter your code here

			/*execute a function when someone clicks in the document:*/
        	if(document.addEventListener)
        	{
				document.addEventListener('click', 	autocomplete_destroy);
			}
			else
			{
            	document.attachEvent('onclick', autocomplete_destroy);
			}
        }
    }

    function readyStateChange() {
        if ( document.readyState === "complete" ) {
            docReady();
        }
    }

    // if document already ready to go, schedule the docReady function to run
    // IE only safe when readyState is "complete", others safe when readyState is "interactive"
    if (document.readyState === "complete" || (!document.attachEvent && document.readyState === "interactive")) {
        setTimeout(docReady, 1);
    } else {
        // otherwise install event handlers
        if (document.addEventListener) {
            // first choice is DOMContentLoaded event
            document.addEventListener("DOMContentLoaded", docReady, false);
            // backup is window load event
            window.addEventListener("load", docReady, false);
        } else {
            // must be IE
            document.attachEvent("onreadystatechange", readyStateChange);
            window.attachEvent("onload", docReady);
        }
    }
})();
