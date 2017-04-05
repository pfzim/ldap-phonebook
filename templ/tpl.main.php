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
		imgblock.style.left = ev.clientX  + "px";
		imgblock.style.top = ev.clientY  + "px";
	}
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
  table = document.getElementById("table");
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

</script>
		<h3 align="center">LDAP phonebook</h3>
		<div class="alert alert-success alert-dismissable collapse" role="alert" id="error">
		  <span>
		  <p>Looks like the passwords you entered don't match!</p>
		  </span>
		</div>
		<div id="imgblock" style="position: fixed; display: none; border: 0px solid black; padding: 0px; margin: 0px;"><img id="userphoto" src=""/></div>
		<input type="text" id="search" onkeyup="filter_table()" placeholder="Search for names..">
		<table id="table" class="main-table">
			<thead>
			<tr>
				<th width="20%">Name</th>
				<th width="10%">Phone</th>
				<th width="10%">Mobile</th>
				<th width="25%">E-Mail</th>
				<th width="10%">Position</th>
				<th width="10%">Department</th>
				<?php if($uid) { ?>
				<th width="5%">Op</th>
				<?php } ?>
			</tr>
			</thead>
			<tbody>
		<?php $i = 0; if($res !== FALSE) foreach($res as $row) { $i++; ?>
			<tr id="<?php eh("row".$row[0]);?>" data-id="<?php eh($row[0]);?>">
				<td onmouseover="si(event, '<?php if(!empty($row[10])) { eh('data:'.$row[10].';base64,'.$row[11]); } ?>');" onmouseout="hide();" style="cursor: pointer;" class="<?php if(!empty($row[10])) { eh('userwithphoto'); } ?>"><?php eh($row[2].' '.$row[3]); ?></td>
				<td><?php eh($row[7]); ?></td>
				<td><?php eh($row[8]); ?></td>
				<td><a href="mailto:<?php eh($row[9]); ?>"><?php eh($row[9]); ?></a></td>
				<td><?php eh($row[6]); ?></td>
				<td><?php eh($row[4]); ?></td>
				<?php if($uid) { ?>
				<td class="command cmd_hide">Hide</td>
				<?php } ?>
			</tr>
		<?php } ?>
			</tbody>
		</table>
		<script>
			$(".cmd_hide").click(function() { var id = $(this).parent().data('id'); $.get("pb.php", {'action': 'hide', 'id': id }, function(data) { 
				$("#error").html($.parseJSON(data).message).show(); 
				$("#row"+id).remove();
				} ) });
		</script>
<?php include("tpl.footer.php"); ?>
