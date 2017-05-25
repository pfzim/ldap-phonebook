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
	var img = ev.target.parentNode.dataset.photo;
	if(parseInt(img, 10))
	{
		var el = gi('userphoto');
		el.src = 'photos/t'+ev.target.parentNode.dataset.id+'.jpg';
		el = gi('imgblock');
		imgblock.style.display = 'block';
		imgblock.style.left = (ev.clientX+10)  + "px";
		imgblock.style.top = (ev.clientY+10)  + "px";
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
	var id = parseInt(ev.target.parentNode.dataset.map, 10);
	if(id)
	{
		var el = gi('map-container');
		var x = parseInt(ev.target.parentNode.dataset.x, 10);
		var y = parseInt(ev.target.parentNode.dataset.y, 10);
		el.style.display = 'block';
		el.onclick = function() {gi('map-container').style.display = 'none';};
		var map = gi('map-image');
		map.onload = function(x, y)
		{
			return function(ev)
			{
				var el = gi('map-marker');
				if(el)
				{
					el.onclick = null;
					el.style.display = 'block';
					el.style.left = (ev.target.offsetLeft + x - el.width/2)  + "px";
					el.style.top = (ev.target.offsetTop + y - el.height/2)  + "px";
					//alert("    x: "+(ev.target.offsetLeft + x) +"    y: "+(ev.target.offsetTop + y));
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
			$.notify(data.message, data.code?"error":"success");
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
	var id = ev.target.parentNode.parentNode.dataset.id;
	var map = ev.target.dataset.map;
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
			f_set_location(id, map, event.pageX - gi('map-image').offsetLeft, event.pageY - gi('map-image').offsetTop);
			gi('map-container').style.display='none';
			gi('map-image').onclick = null;
		};
	};
};

function f_hide(ev)
{
	var id = ev.target.parentNode.parentNode.dataset.id;
	f_http("pb.php?"+json2url({'action': 'hide', 'id': id }),
		function(data, el)
		{ 
			$.notify(data.message, data.code?"error":"success");
			if(!data.code)
			{
				el.textContent = 'Show';
				el.onclick = function(event) { f_show(event); };
			}
		},
		ev.target
	);
};

function f_show(ev)
{
	var id = ev.target.parentNode.parentNode.dataset.id;
	f_http("pb.php?"+json2url({'action': 'show', 'id': id }),
		function(data, el)
		{
			$.notify(data.message, data.code?"error":"success");
			if(!data.code)
			{
				el.textContent = 'Hide';
				el.onclick = function(event) { f_hide(event); };
			}
		},
		ev.target,
	);
};

function f_delete(ev)
{
	var id = ev.target.parentNode.parentNode.dataset.id;
	f_http("pb.php?"+json2url({'action': 'delete', 'id': id }),
		function(data, el)
		{
			$.notify(data.message, data.code?"error":"success");
			if(!data.code)
			{
				var row = el.parentNode.parentNode;
				row.parentNode.removeChild(row);

			}
		},
		ev.target
	);
};

function f_save()
{
	f_http("pb.php?action=save&id="+gi('edit_id').value, 
		function(data, params)
		{
			$.notify(data.message, data.code?"error":"success");
			if(!data.code)
			{
				gi('edit-container').style.display='none';
				f_update_row(data.id);
			}
		},
		null,
		'application/x-www-form-urlencoded',
		json2url(
		{
			'firstname': gi('firstname').value,
			'lastname': gi('lastname').value,
			'department': gi('department').value,
			'company': gi('company').value,
			'position': gi('position').value,
			'phone': gi('phone').value,
			'mobile': gi('mobile').value,
			'mail': gi('mail').value
		})
	);
}

function f_update_row(id)
{
	f_http("pb.php?"+json2url({'action': 'get', 'id': id }),
		function(data, params)
		{
			if(data.code)
			{
				$.notify(data.message, "error");
			}
			else
			{
				var row = gi('row'+data.id);
				if(!row)
				{
					row = gi("table-data").insertRow(0);
					row.insertCell(0);
					row.insertCell(1);
					row.insertCell(2);
					row.insertCell(3);
					row.insertCell(4);
					row.insertCell(5);
					row.insertCell(6);
				}
				
				row.id = 'row'+data.id;
				row.setAttribute("data-id", data.id);
				row.setAttribute("data-map", data.map);
				row.setAttribute("data-x", data.x);
				row.setAttribute("data-y", data.y);
				row.setAttribute("data-photo", data.photo);
				row.cells[0].textContent = data.firstname + ' ' + data.lastname;
				if(data.photo)
				{
					row.cells[0].className = 'userwithphoto';
				}
				row.cells[0].style.cursor = 'pointer';
				row.cells[0].onclick = function(event) { f_sw_map(event); };
				row.cells[0].onmouseenter = function(event) { f_sw_img(event); };
				row.cells[0].onmouseleave = function(event) { gi('imgblock').style.display = 'none'; };
				row.cells[0].onmousemove = function(event) { f_mv_img(event); };
				
				row.cells[1].textContent = data.phone;
				row.cells[2].textContent = data.mobile;
				row.cells[3].innerHTML = '<a href="mailto:'+escapeHtml(data.mail)+'">'+escapeHtml(data.mail)+'</a>';
				row.cells[4].textContent = data.position;
				row.cells[5].textContent = data.department;
				
				var str = '<span class="command" onclick="f_edit(event);">Edit</span> <span class="command" onclick="f_delete(event);">Delete</span> <span class="command" onclick="f_photo(event);">Photo</span> <span class="command" data-map="1" onclick="f_map_set(event);">Map&nbsp;1</span>';
				for(i = 2; i <= count_maps; i++)
				{
					str += ' <span class="command" data-map="'+i+'" onclick="f_map_set(event);">'+i+'</span>';
				}
				
				if(data.visible)
				{
					row.cells[6].innerHTML = str+' <span class="command" onclick="f_hide(event);">Hide</span>';
				}
				else
				{
					row.cells[6].innerHTML = str+' <span class="command" onclick="f_show(event);">Show</span>';
				}
				//row.cells[6].onclick = function(event) { h(event); };
			}
		}
	);
}

function f_edit(ev)
{
	var id = 0;
	if(ev)
	{
		id = ev.target.parentNode.parentNode.dataset.id;
	}
	gi('edit_id').value = id;
	if(!id)
	{
		gi('firstname').value = '';
		gi('lastname').value = '';
		gi('department').value = '';
		gi('company').value = '';
		gi('position').value = '';
		gi('phone').value = '';
		gi('mobile').value = '';
		gi('mail').value = '';
		gi('edit-container').style.display='block';
	}
	else
	{
		f_http("pb.php?"+json2url({'action': 'get', 'id': id }),
			function(data, params)
			{
				if(data.code)
				{
					$.notify(data.message, "error");
				}
				else
				{
					gi('firstname').value = data.firstname;
					gi('lastname').value = data.lastname;
					gi('department').value = data.department;
					gi('company').value = data.company;
					gi('position').value = data.position;
					gi('phone').value = data.phone;
					gi('mobile').value = data.mobile;
					gi('mail').value = data.mail;
					gi('edit-container').style.display='block';
				}
			}
		);
	}
}

function f_upload(id)
{
	var fd = new FormData(gi("photo-upload"));
	f_http("pb.php?action=setphoto&id="+id,
		function(data, params)
		{
			$.notify(data.message, data.code?"error":"success");
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

function f_photo(ev)
{
	var id = 0;
	if(ev)
	{
		id = ev.target.parentNode.parentNode.dataset.id;
	}
	if(id)
	{
		gi('upload').onchange = function(id) {
			return function() {
				f_upload(id);
			}
		}(id);
		gi('upload').click();
	}
}

function si(ev)
{
	document.getElementById('popup').style.display = 'block';
	document.getElementById('popup').style.left = (ev.pageX+10)  + "px";
	document.getElementById('popup').style.top = (ev.pageY+10)  + "px";
	if(parseInt(ev.target.dataset.photo, 10))
	{
		document.getElementById('u_photo').src = 'photos/t'+ev.target.dataset.id+'.jpg';
	}
	else
	{
		document.getElementById('u_photo').src = 'templ/nophoto.png';
	}
	document.getElementById('u_name').textContent = ev.target.dataset.name;
	document.getElementById('u_position').textContent = ev.target.dataset.position;
	document.getElementById('u_phone').textContent = ev.target.dataset.phone;
}

function mi(ev)
{
	var el = document.getElementById('popup');
	if(el)
	{
		el.style.left = (ev.pageX+10)  + "px";
		el.style.top = (ev.pageY+10)  + "px";
	}
}

/* old function click-click-move
function f_click(ev)
{
	ev.target.style.border="1px dashed red";
	ev.target.style.borderRadius = "5px";
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
	}(ev.target.dataset.id);
}
*/

function f_drag(ev)
{
	ev.target.style.border="1px dashed red";
	ev.target.style.borderRadius = "5px";

	var box = document.getElementById('map-image').getBoundingClientRect();
	var sx = (window.pageXOffset !== undefined)? window.pageXOffset: (document.documentElement || document.body.parentNode || document.body).scrollLeft;
	var sy = (window.pageYOffset !== undefined)? window.pageYOffset: (document.documentElement || document.body.parentNode || document.body).scrollTop;
	ev.target.style.left = Math.round(ev.pageX - box.left - sx - 17)+'px';
	ev.target.style.top = Math.round(ev.pageY - box.top - sy - 23)+'px';

	document.onmousemove = function(id)
	{
		return function(ev)
		{
			var box = document.getElementById('map-image').getBoundingClientRect();
			var sx = (window.pageXOffset !== undefined)? window.pageXOffset: (document.documentElement || document.body.parentNode || document.body).scrollLeft;
			var sy = (window.pageYOffset !== undefined)? window.pageYOffset: (document.documentElement || document.body.parentNode || document.body).scrollTop;
			var x = Math.round(ev.pageX - box.left - sx);
			var y = Math.round(ev.pageY - box.top - sy);
			if(x < 0) x = 0;
			if(y < 0) y = 0;
			if(x > box.right - box.left) x = box.right - box.left;
			if(y > box.bottom - box.top) y = box.bottom - box.top;
			//console.log("onmousemove "+id+"    sx "+sy+"     sY "+window.scrollY);
			document.getElementById('u'+id).style.left = (x - 17)+'px';
			document.getElementById('u'+id).style.top = (y - 23)+'px';
		}
	}(ev.target.dataset.id);

	ev.target.onmouseup = function(ev) { f_drop(ev) };
}

function f_drop(ev)
{
	document.onmousemove = null;
	var box = document.getElementById('map-image').getBoundingClientRect();
	//alert('px: '+ev.pageX+'  py: '+ev.pageY+'   cx: '+(box.left)+'  py: '+(box.top));
	var sx = (window.pageXOffset !== undefined)? window.pageXOffset: (document.documentElement || document.body.parentNode || document.body).scrollLeft;
	var sy = (window.pageYOffset !== undefined)? window.pageYOffset: (document.documentElement || document.body.parentNode || document.body).scrollTop;
	var x = Math.round(ev.pageX - box.left - sx);
	var y = Math.round(ev.pageY - box.top - sy);
	if(x < 0) x = 0;
	if(y < 0) y = 0;
	if(x > box.right - box.left) x = box.right - box.left;
	if(y > box.bottom - box.top) y = box.bottom - box.top;
	ev.target.style.left = (x - 16)+'px';
	ev.target.style.top = (y - 22)+'px';
	f_set_location(ev.target.dataset.id, map, x, y);
	ev.target.style.border="0px dashed black";
	ev.target.onmouseup = null;
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
		  if (tds[j].textContent.toLowerCase().indexOf(filter) > -1)
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
