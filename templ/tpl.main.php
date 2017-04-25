<?php include("tpl.header.php"); ?>
<script>

function si(ev, img)
{
	if(img)
	{
		var el = document.getElementById('userphoto');
		el.src = img;
		el = document.getElementById('imgblock');
		imgblock.style.display = 'block';
		imgblock.style.left = (ev.clientX+10)  + "px";
		imgblock.style.top = (ev.clientY+10)  + "px";
	}
}

function mi(ev)
{
	var el = document.getElementById('imgblock');
	if(el)
	{
		el.style.left = (ev.clientX+10)  + "px";
		el.style.top = (ev.clientY+10)  + "px";
	}
}

function sm(id, x, y)
{
	var el = document.getElementById('map-container');
	el.style.display = 'block';
	el.onclick = function() {document.getElementById('map-container').style.display = 'none';};
	var map = document.getElementById('map-image');
	map.onload = function()
	{
		var el = document.getElementById('map-marker');
		if(el)
		{
			el.onclick = null;
			el.style.display = 'block';
			el.style.left = (this.offsetLeft + x - el.width/2)  + "px";
			el.style.top = (this.offsetTop + y - el.height/2)  + "px";
		}
	};
	map.src = 'templ/map' + id + '.png';
}

function f_set_location(id, map, x, y)
{
	//alert("map: "+map+"    x: "+x+"    y: "+y);
	$.post("pb.php?action=setlocation&id="+id, {'map': map, 'x': x, 'y': y },
		function(data)
		{
			$.notify(data.message, "success");
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

function hide(img)
{
	var el = document.getElementById('imgblock');
	imgblock.style.display = 'none';
}

function filter_table() {
  // Declare variables 
  var input, filter, table, tr, td, i;
  input = document.getElementById("search");
  filter = input.value.toLowerCase();
  table = document.getElementById("table-data");
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
  table = document.getElementById("table");
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
		<input type="text" id="search" onkeyup="filter_table()" placeholder="Search..">
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
			<tr id="<?php eh("row".$row[0]);?>" data-id="<?php eh($row[0]);?>">
				<td <?php if(!empty($row[12])) { ?> onclick="sm(<?php eh($row[12].', '.$row[13].', '.$row[14]);?>);" <?php } ?>onmouseenter="si(event, '<?php if(!empty($row[10])) { eh('data:'.$row[10].';base64,'.$row[11]); } ?>');" onmouseleave="hide();" onmousemove="mi(event);" style="cursor: pointer;" class="<?php if(!empty($row[10])) { eh('userwithphoto'); } ?>"><?php eh($row[2].' '.$row[3]); ?></td>
				<td><?php eh($row[7]); ?></td>
				<td><?php eh($row[8]); ?></td>
				<td><a href="mailto:<?php eh($row[9]); ?>"><?php eh($row[9]); ?></a></td>
				<td><?php eh($row[6]); ?></td>
				<td><?php eh($row[4]); ?></td>
				<?php if($uid) { ?>
				<td><span class="command cmd_loc_1">Map&nbsp;1</span> <span class="command cmd_loc_2">2</span> <span class="command cmd_loc_3">3</span> <span class="command cmd_loc_4">4</span> <span class="command cmd_loc_5">5</span> <?php if($row[15]) { ?><span class="command cmd_hide">Hide</span><?php } else { ?><span class="command cmd_show">Show</span><?php } ?></td>
				<?php } ?>
			</tr>
		<?php } ?>
			</tbody>
		</table>
		<div id="map-container" class="map-container" style="display:none">
				<img id="map-image" class="map-image" src="templ/map1.png"/>
				<img id="map-marker" class="map-marker" src="templ/marker.gif"/>
				<span class="close" onclick="this.parentNode.style.display='none'">&times;</span>
		</div>
		<script>
			var i;
			var tags;
			tags = document.getElementsByClassName('cmd_hide');
			for(i = 0; i < tags.length; i++)
			{
				tags[i].onclick = function()
				{
					var id = this.parentNode.parentNode.dataset.id;
					$.get("pb.php", {'action': 'hide', 'id': id },
						function(data)
						{ 
							$.notify(data.message, "success");
						},
						'json'
					)
					.fail(
						function()
						{
							$.notify("Failed AJAX request", "error");
						}
					)
				};
			}
			
			tags = document.getElementsByClassName('cmd_show');
			for(i = 0; i < tags.length; i++)
			{
				tags[i].onclick = function()
				{
					var id = this.parentNode.parentNode.dataset.id;
					$.get("pb.php", {'action': 'show', 'id': id },
						function(data)
						{ 
							$.notify(data.message, "success");
						},
						'json'
					)
					.fail(
						function()
						{
							$.notify("Failed AJAX request", "error");
						}
					)
				};
			}

			for(i = 1; i <= 5; i++)
			{
				var j;
				tags = document.getElementsByClassName('cmd_loc_'+i);
				for(j = 0; j < tags.length; j++)
				{
					tags[j].onclick = function(i)
					{
						return function()
						{
							var id = this.parentNode.parentNode.dataset.id;
							document.getElementById('map-container').onclick = null;
							document.getElementById('map-image').onload = null;
							document.getElementById('map-image').src = 'templ/map'+i+'.png';
							document.getElementById('map-container').style.display='block';
							document.getElementById('map-marker').style.display='none';
							document.getElementById('map-image').onclick = function(event)
							{
								document.getElementById('map-marker').style.display='block';
								document.getElementById('map-marker').style.left = (event.clientX - document.getElementById('map-marker').width/2)  + "px";
								document.getElementById('map-marker').style.top = (event.clientY - document.getElementById('map-marker').height/2)  + "px";
								document.getElementById('map-marker').onclick = function()
								{
									f_set_location(id, i, event.pageX - document.getElementById('map-image').offsetLeft, event.pageY - document.getElementById('map-image').offsetTop);
									document.getElementById('map-container').style.display='none';
									document.getElementById('map-image').onclick = null;
								};
							};
						};
					} (i);
				}
			}
		</script>
<?php include("tpl.footer.php"); ?>
