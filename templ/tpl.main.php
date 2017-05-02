<?php include("tpl.header.php"); ?>
<script>

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

function f_sw_img(ev)
{
	var img = ev.target.parentNode.dataset.photo;
	if(img)
	{
		var el = gi('userphoto');
		el.src = img;
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
	$.post("pb.php?action=setlocation&id="+id, {'map': map, 'x': x, 'y': y },
		function(data)
		{
			$.notify(data.message, data.result?"error":"success");
			var row = gi('row'+data.id);
			if(row)
			{
				row.setAttribute("data-map", data.map);
				row.setAttribute("data-x", data.x);
				row.setAttribute("data-y", data.y);
			}
		},
		'json'
	)
	.fail(
		function()
		{
			$.notify("Failed AJAX request", "error");
		}
	)
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
	$.get("pb.php", {'action': 'hide', 'id': id },
		function(el)
		{
			return function(data)
			{ 
				$.notify(data.message, data.result?"error":"success");
				el.textContent = 'Show';
				el.onclick = function(event) { f_show(event); };
			}
		}(ev.target),
		'json'
	)
	.fail(
		function()
		{
			$.notify("Failed AJAX request", "error");
		}
	)
};

function f_show(ev)
{
	var id = ev.target.parentNode.parentNode.dataset.id;
	$.get("pb.php", {'action': 'show', 'id': id },
		function(el)
		{
			return function(data)
			{
				$.notify(data.message, data.result?"error":"success");
				el.textContent = 'Hide';
				el.onclick = function(event) { f_hide(event); };
			}
		}(ev.target),
		'json'
	)
	.fail(
		function()
		{
			$.notify("Failed AJAX request", "error");
		}
	)
};

function f_delete(ev)
{
	var id = ev.target.parentNode.parentNode.dataset.id;
	$.get("pb.php", {'action': 'delete', 'id': id },
		function(el)
		{
			return function(data)
			{
				$.notify(data.message, data.result?"error":"success");
				if(!data.result)
				{
					var row = el.parentNode.parentNode;
					row.parentNode.removeChild(row);

				}
			}
		}(ev.target),
		'json'
	)
	.fail(
		function()
		{
			$.notify("Failed AJAX request", "error");
		}
	)
};

function f_save()
{
	$.post("pb.php?action=save&id="+gi('edit_id').value, 
		{
			'firstname': gi('firstname').value,
			'lastname': gi('lastname').value,
			'department': gi('department').value,
			'company': gi('company').value,
			'position': gi('position').value,
			'phone': gi('phone').value,
			'mobile': gi('mobile').value,
			'mail': gi('mail').value
		},
		function(data)
		{
			$.notify(data.message, data.result?"error":"success");
			if(!data.result)
			{
				gi('edit-container').style.display='none';
				f_update_row(data.id);
			}
		},
		'json'
	)
	.fail(
		function()
		{
			$.notify("Failed AJAX request", "error");
		}
	)
}

function f_update_row(id)
{
	$.get("pb.php", {'action': 'get', 'id': id },
		function(val)
		{
			return function(data)
			{
				if(data.result)
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
					row.setAttribute("data-photo", data.photo?'data:'+data.mime+';base64,'+data.photo:'');
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
					if(parseInt(data.visible, 10))
					{
						row.cells[6].innerHTML = '<span class="command" onclick="f_edit(event);">Edit</span> <span class="command" onclick="f_delete(event);">Delete</span> <span class="command" data-map="1" onclick="f_map_set(event);">Map&nbsp;1</span><?php for($i = 2; $i <= PB_MAPS_COUNT; $i++) { ?> <span class="command" data-map="<?php eh($i); ?>" onclick="f_map_set(event);"><?php eh($i); ?></span><?php } ?> <span class="command" onclick="f_hide(event);">Hide</span>';
					}
					else
					{
						row.cells[6].innerHTML = '<span class="command" onclick="f_edit(event);">Edit</span> <span class="command" onclick="f_delete(event);">Delete</span> <span class="command" data-map="1" onclick="f_map_set(event);">Map&nbsp;1</span><?php for($i = 2; $i <= PB_MAPS_COUNT; $i++) { ?> <span class="command" data-map="<?php eh($i); ?>" onclick="f_map_set(event);"><?php eh($i); ?></span><?php } ?> <span class="command" onclick="f_show(event);">Show</span>';
					}
					//row.cells[6].onclick = function(event) { h(event); };
				}
			}
		}(0),
		'json'
	)
	.fail(
		function()
		{
			$.notify("Failed AJAX request", "error");
		}
	)
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
		$.get("pb.php", {'action': 'get', 'id': id },
			function(el)
			{
				return function(data)
				{
					if(data.result)
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
			}(ev.target),
			'json'
		)
		.fail(
			function()
			{
				$.notify("Failed AJAX request", "error");
			}
		)
	}
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
	for (var td of tds)
	{
		if (td)
		{
		  if (td.textContent.toLowerCase().indexOf(filter) > -1)
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
</script>
		<h3 align="center">LDAP Phonebook</h3>
		<div id="imgblock" style="position: fixed; display: none; border: 0px solid black; padding: 0px; margin: 0px;"><img id="userphoto" src=""/></div>
		<input type="text" id="search" class="form-field" onkeyup="filter_table()" placeholder="Search..">
		<?php if($uid) { ?>
		<span class="command f-right" onclick="f_edit(null);">Add contact</span>
		<?php } ?>
		<table id="table" class="main-table">
			<thead>
			<tr>
				<th width="20%" onclick="sortTable(0)">Name</th>
				<th width="10%" onclick="sortTable(1)">Phone</th>
				<th width="10%" onclick="sortTable(2)">Mobile</th>
				<th width="25%" onclick="sortTable(3)">E-Mail</th>
				<th width="10%" onclick="sortTable(4)">Position</th>
				<th width="10%" onclick="sortTable(5)">Department</th>
				<?php if($uid) { ?>
				<th width="5%">Operations</th>
				<?php } ?>
			</tr>
			</thead>
			<tbody id="table-data">
		<?php $i = 0; if($db->data !== FALSE) foreach($db->data as $row) { $i++; ?>
			<tr id="<?php eh("row".$row[0]);?>" data-id="<?php eh($row[0]);?>" data-map="<?php eh($row[12]); ?>" data-x="<?php eh($row[13]); ?>" data-y="<?php eh($row[14]); ?>" data-photo="<?php if(!empty($row[10])) { eh('data:'.$row[10].';base64,'.$row[11]); } ?>">
				<td onclick="f_sw_map(event);" onmouseenter="f_sw_img(event);" onmouseleave="gi('imgblock').style.display = 'none'" onmousemove="f_mv_img(event);" style="cursor: pointer;" class="<?php if(!empty($row[10])) { eh('userwithphoto'); } ?>"><?php eh($row[2].' '.$row[3]); ?></td>
				<td><?php eh($row[7]); ?></td>
				<td><?php eh($row[8]); ?></td>
				<td><a href="mailto:<?php eh($row[9]); ?>"><?php eh($row[9]); ?></a></td>
				<td><?php eh($row[6]); ?></td>
				<td><?php eh($row[4]); ?></td>
				<?php if($uid) { ?>
				<td>
					<?php if(empty($row[1])) { ?>
						<span class="command" onclick="f_edit(event);">Edit</span>
						<span class="command" onclick="f_delete(event);">Delete</span>
					<?php } ?>
					<span class="command" data-map="1" onclick="f_map_set(event);">Map&nbsp;1</span>
					<?php for($i = 2; $i <= PB_MAPS_COUNT; $i++) { ?>
						<span class="command" data-map="<?php eh($i); ?>" onclick="f_map_set(event);"><?php eh($i); ?></span>
					<?php } ?>
					<?php if($row[15]) { ?>
						<span class="command" onclick="f_hide(event);">Hide</span>
					<?php } else { ?>
						<span class="command" onclick="f_show(event);">Show</span>
					<?php } ?>
				</td>
				<?php } ?>
			</tr>
		<?php } ?>
			</tbody>
		</table>
		<div id="edit-container" class="modal-container" style="display: none">
			<span class="close" onclick="this.parentNode.style.display='none'">&times;</span>
			<div class="modal-content">
				<h3>Contact</h3>
				<input id="edit_id" type="hidden" value=""/>
				<div class="form-title"><label for="firstname">First name:</label></div>
				<input class="form-field" id="firstname" type="edit" value=""/>
				<div class="form-title"><label for="lastname">Last name:</label></div>
				<input class="form-field" id="lastname" type="edit" value=""/>
				<div class="form-title"><label for="company">Company:</label></div>
				<input class="form-field" id="company" type="edit" value=""/>
				<div class="form-title"><label for="department">Department:</label></div>
				<input class="form-field" id="department" type="edit" value=""/>
				<div class="form-title"><label for="position">Position:</label></div>
				<input class="form-field" id="position" type="edit" value=""/>
				<div class="form-title"><label for="phone">Phone:</label></div>
				<input class="form-field" id="phone" type="edit" value=""/>
				<div class="form-title"><label for="mobile">Mobile:</label></div>
				<input class="form-field" id="mobile" type="edit" value=""/>
				<div class="form-title"><label for="mail">E-mail:</label></div>
				<input class="form-field" id="mail" type="edit" value=""/><br />
				<button class="form-button" type="button" onclick="f_save();">Save</button>
			</div>
		</div>
		<div id="map-container" class="modal-container" style="display:none">
			<span class="close" onclick="this.parentNode.style.display='none'">&times;</span>
			<img id="map-image" class="map-image" src="templ/map1.png"/>
			<img id="map-marker" class="map-marker" src="templ/marker.gif"/>
		</div>
<?php include("tpl.footer.php"); ?>
