var map = 0;
var map_count = 0;

function gi(name)
{
	return document.getElementById(name);
}

function escapeHtml(text)
{
  return text
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
}

function json2url(data)
{
	return Object.keys(data).map
	(
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

function f_sw_img(ev)
{
	var el_src = ev.target || ev.srcElement;
	var img = el_src.parentNode.getAttribute('data-photo');
	if(parseInt(img, 10))
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
		map.src = 'templ/map' + id + '.png';
	}
}

function f_set_location(id, map, x, y)
{
	//alert("map: "+map+"    x: "+x+"    y: "+y);
	f_http("pb.php?action=setlocation&id="+id,
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
		json2url({'map': map, 'x': x, 'y': y })
	);
}

function f_map_set(ev)
{
	var el_src = ev.target || ev.srcElement;
	var id = el_src.parentNode.parentNode.parentNode.getAttribute('data-id');
	var map = el_src.getAttribute('data-map');
	gi('map-container').onclick = null;
	gi('map-image').onload = null;
	gi('map-image').src = 'templ/map'+map+'.png';
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
	f_http("pb.php?"+json2url({'action': 'hide', 'id': id }),
		function(data, el)
		{
			f_notify(data.message, data.code?"error":"success");
			if(!data.code)
			{
				el.textContent = 'Show';
				el.onclick = function(event) { f_show(event); };
			}
		},
		el_src
	);
};

function f_hide2(ev)
{
	var el_src = ev.target || ev.srcElement;
	var id = el_src.parentNode.parentNode.parentNode.getAttribute('data-id');
	f_http("pb.php?"+json2url({'action': 'hide', 'id': id }),
		function(data, el)
		{
			f_notify(data.message, data.code?"error":"success");
			if(!data.code)
			{
				gi('menu-cmd-hide').style.display = 'none';
				gi('menu-cmd-show').style.display = 'block';
			}
		},
		el_src
	);
};

function f_show(ev)
{
	var el_src = ev.target || ev.srcElement;
	var id = el_src.parentNode.parentNode.parentNode.getAttribute('data-id');
	f_http("pb.php?"+json2url({'action': 'show', 'id': id }),
		function(data, el)
		{
			f_notify(data.message, data.code?"error":"success");
			if(!data.code)
			{
				el.textContent = 'Hide';
				el.onclick = function(event) { f_hide(event); };
			}
		},
		el_src
	);
};

function f_show2(ev)
{
	var el_src = ev.target || ev.srcElement;
	var id = el_src.parentNode.parentNode.parentNode.getAttribute('data-id');
	f_http("pb.php?"+json2url({'action': 'show', 'id': id }),
		function(data, el)
		{
			f_notify(data.message, data.code?"error":"success");
			if(!data.code)
			{
				gi('menu-cmd-hide').style.display = 'block';
				gi('menu-cmd-show').style.display = 'none';
			}
		},
		el_src
	);
};

function f_get_acs_location(ev)
{
	var el_src = ev.target || ev.srcElement;
	var id = el_src.parentNode.parentNode.parentNode.getAttribute('data-id');
	f_http("pb.php?"+json2url({'action': 'get_acs_location', 'id': id }),
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
	f_http("pb.php?"+json2url({'action': 'delete', 'id': id }),
		function(data, el)
		{
			f_notify(data.message, data.code?"error":"success");
			if(!data.code)
			{
				var row = el.parentNode.parentNode;
				row.parentNode.removeChild(row);

			}
		},
		el_src
	);
};


function f_save(form_id)
{
	var form_data = {};
	var el = gi(form_id);
	for(i = 0; i < el.elements.length; i++)
	{
		var err = gi(el.elements[i].name + '-error');
		if(err)
		{
			err.style.display='none';
		}
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

	gi('loading').style.display = 'block';
	f_http("pb.php?action=save",
		function(data, params)
		{
			gi('loading').style.display = 'none';
			f_notify(data.message, data.code?"error":"success");
			if(!data.code)
			{
				gi(params+'-container').style.display='none';
				f_update_row(data.id);
			}
			else if(data.errors)
			{
				for(i = 0; i < data.errors.length; i++)
				{
					var el = gi(data.errors[i].name + "-error");
					if(el)
					{
						el.textContent = data.errors[i].msg;
						el.style.display='block';
					}
				}
			}
		},
		form_id,
		'application/x-www-form-urlencoded',
		json2url(form_data)
	);
}

function f_update_row(id)
{
	f_http("pb.php?"+json2url({'action': 'get_contact', 'id': id }),
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
				row.setAttribute("data-photo", 	data.data.photo);
				
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
				nameCell.textContent 		= data.data.firstname + ' ' + data.data.lastname;
				if(data.data.photo) {
					nameCell.className = 'userwithphoto';
				}
				nameCell.style.cursor 		= 'pointer';
				nameCell.onclick 			= function(event) { f_sw_map(event); };
				nameCell.onmouseenter 		= function(event) { f_sw_img(event); };
				nameCell.onmouseleave 		= function(event) { gi('imgblock').style.display = 'none'; };
				nameCell.onmousemove 		= function(event) { f_mv_img(event); };
				
				phoneCell.textContent 		= data.data.phone;				
				phoneCityCell.textContent 	= data.data.phonecity;
				mobileCell.textContent 		= data.data.mobile;
				mailCell.innerHTML 			= '<a href="mailto:'+escapeHtml(data.data.mail)+'">'+escapeHtml(data.data.mail)+'</a>';
				positionCell.textContent 	= data.data.position;
				departmentCell.textContent 	= data.data.department;
				menuCell.innerHTML 			= '<span class="command" onclick="f_menu(event);">Menu</span>';
			}
		}
	);
}

function f_edit(ev, form_id)
{
	var id = 0;
	var el_src;
	if(ev)
	{
		el_src = ev.target || ev.srcElement;
		id = el_src.parentNode.parentNode.parentNode.getAttribute('data-id');
		//id = ev;
	}
	if(!id)
	{
		var form_data = {};
		var el = gi(form_id);
		for(i = 0; i < el.elements.length; i++)
		{
			var err = gi(el.elements[i].name + '-error');
			if(err)
			{
				err.style.display='none';
			}
			if(el.elements[i].name == 'id')
			{
				el.elements[i].value = id;
			}
			else if(el.elements[i].name == 'pid')
			{
				el.elements[i].value = g_pid;
			}
			else
			{
				if(el.elements[i].type == 'checkbox')
				{
					el.elements[i].checked = false;
				}
				else
				{
					el.elements[i].value = '';
				}
			}
		}
		gi(form_id + '-container').style.display='block';
	}
	else
	{
		gi('loading').style.display = 'block';
		f_http("pb.php?"+json2url({'action': 'get_' + form_id, 'id': id }),
			function(data, params)
			{
				gi('loading').style.display = 'none';
				if(data.code)
				{
					f_notify(data.message, "error");
				}
				else
				{
					var el = gi(params);
					for(i = 0; i < el.elements.length; i++)
					{
						if(el.elements[i].name)
						{
							if(data.data[el.elements[i].name])
							{
								if(el.elements[i].type == 'checkbox')
								{
									el.elements[i].checked = (parseInt(data.data[el.elements[i].name], 10) != 0);
								}
								else
								{
									el.elements[i].value = data.data[el.elements[i].name];
								}
							}
						}
					}
					gi(params+'-container').style.display='block';
				}
			},
			form_id
		);
	}
}

function f_upload_photo(id)
{
	gi('loading').style.display = 'block';
	var fd = new FormData(gi("form-file-upload"));
	f_http("pb.php?action=setphoto&id="+id,
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
	f_http("pb.php?action="+action,
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
		gi('menu-cmd-show').style.display = 'none';
		gi('menu-cmd-hide').style.display = 'none';
		gi('menu-cmd-connect-0').style.display = 'none';
		gi('menu-cmd-connect-1').style.display = 'none';
		gi('menu-cmd-connect-2').style.display = 'none';
		gi('menu-loading').style.display = 'block';
		el.style.display = 'block';
		parentElement = el_src;
		document.addEventListener('click', documentClick, false);
		
		f_http("pb.php?"+json2url({'action': 'get_contact', 'id': id }),
			function(data, el)
			{
				gi('menu-loading').style.display = 'none';
				if(!data.code)
				{
					// add pc to list
					if(data.code)
					{
						f_notify(data.message, "error");
					}
					else
					{
						if(data.data.samname == '')
						{
							gi('menu-cmd-edit').style.display = 'block';
							gi('menu-cmd-photo').style.display = 'block';
							gi('menu-cmd-delete').style.display = 'block';
						}
						if(data.data.visible)
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
							if(data.data.pc[i] != '')
							{
								gi('menu-cmd-connect-'+i).href = 'msraurl:' + data.data.pc[i];
								gi('menu-cmd-connect-'+i).style.display = 'block';
								gi('menu-cmd-connect-'+i).textContent = 'Connect to ' + data.data.pc[i];
							}
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
			f_upload_file('import_xml');
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
			"/zxsa.php?action=hide_selected",
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
	if(parseInt(el_src.getAttribute('data-photo'), 10))
	{
		document.getElementById('u_photo').src = 'photos/t'+el_src.getAttribute('data-id')+'.jpg';
	}
	else
	{
		document.getElementById('u_photo').src = 'templ/nophoto.png';
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

function sortTable(n) {
  var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
  table = gi("table");
  switching = true;
  //Set the sorting direction to ascending:
  dir = "asc";
  /*Make a loop that will continue until
  no switching has been done:*/
  while (switching) {
    //start by saying: no switching is done:
    switching = false;
    rows = table.getElementsByTagName("TR");
	if(rows.length > 300) return;
    /*Loop through all table rows (except the
    first, which contains table headers):*/
    for (i = 1; i < (rows.length - 1); i++) {
      //start by saying there should be no switching:
      shouldSwitch = false;
      /*Get the two elements you want to compare,
      one from current row and one from the next:*/
      x = rows[i].getElementsByTagName("TD")[n];
      y = rows[i + 1].getElementsByTagName("TD")[n];
      /*check if the two rows should switch place,
      based on the direction, asc or desc:*/
      if (dir == "asc") {
        if (x.textContent.toLowerCase() > y.textContent.toLowerCase()) {
          //if so, mark as a switch and break the loop:
          shouldSwitch= true;
          break;
        }
      } else if (dir == "desc") {
        if (x.textContent.toLowerCase() < y.textContent.toLowerCase()) {
          //if so, mark as a switch and break the loop:
          shouldSwitch= true;
          break;
        }
      }
    }
    if (shouldSwitch) {
      /*If a switch has been marked, make the switch
      and mark that a switch has been done:*/
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
      //Each time a switch is done, increase this count by 1:
      switchcount ++;
    } else {
      /*If no switching has been done AND the direction is "asc",
      set the direction to "desc" and run the while loop again.*/
      if (switchcount == 0 && dir == "asc") {
        dir = "desc";
        switching = true;
      }
    }
  }
}
