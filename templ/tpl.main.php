<?php include("tpl.header.php"); ?>
		<h3 align="center">LDAP Phonebook</h3>
		<div id="imgblock" class="user-photo"><img id="userphoto" src=""/></div>
		<input type="text" id="search" class="form-field" onkeyup="filter_table()" placeholder="Search..">
		<?php if($uid) { ?>
		<span class="command f-right" onclick="f_edit(null);">Add contact</span>
		<?php } ?>
		<table id="table" class="main-table">
			<thead>
			<tr>
				<?php $i = 0; ?>
				<?php if($uid) { ?>
				<th width="1%"><input type="checkbox" onclick="f_select_all(event)"/></th>
				<?php $i++; } ?>
				<th width="20%" onclick="sortTable(<?php eh($i++); ?>)">Name</th>
				<th width="10%" onclick="sortTable(<?php eh($i++); ?>)">Phone</th>
				<th width="10%" onclick="sortTable(<?php eh($i++); ?>)">Mobile</th>
				<th width="25%" onclick="sortTable(<?php eh($i++); ?>)">E-Mail</th>
				<th width="10%" onclick="sortTable(<?php eh($i++); ?>)">Position</th>
				<th width="10%" onclick="sortTable(<?php eh($i++); ?>)">Department</th>
				<?php if($uid) { ?>
				<th width="15%">Operations</th>
				<?php } ?>
			</tr>
			</thead>
			<tbody id="table-data">
		<?php $i = 0; foreach($db->data as $row) { $i++; ?>
			<tr id="<?php eh("row".$row[0]);?>" data-id=<?php eh($row[0]);?> data-map=<?php eh($row[11]); ?> data-x=<?php eh($row[12]); ?> data-y=<?php eh($row[13]); ?> data-photo=<?php eh($row[10]); ?>>
				<?php if($uid) { ?>
				<td><input type="checkbox" name="check" value="<?php eh($row[0]); ?>"/></td>
				<?php } ?>
				<td onclick="f_sw_map(event);" onmouseenter="f_sw_img(event);" onmouseleave="gi('imgblock').style.display = 'none'" onmousemove="f_mv_img(event);" style="cursor: pointer;" class="<?php if(intval($row[10])) { eh('userwithphoto'); } ?>"><?php eh($row[2].' '.$row[3]); ?></td>
				<td class="command" onclick="f_get_acs_location(event);"><?php eh($row[7]); ?></td>
				<td><?php eh($row[8]); ?></td>
				<td><a href="mailto:<?php eh($row[9]); ?>"><?php eh($row[9]); ?></a></td>
				<td><?php eh($row[6]); ?></td>
				<td><?php eh($row[4]); ?></td>
				<?php if($uid) { ?>
				<td>
					<?php if(empty($row[1])) { ?>
						<span class="command" onclick="f_edit(event);">Edit</span>
						<span class="command" onclick="f_delete(event);">Delete</span>
						<span class="command" onclick="f_photo(event);">Photo</span>
					<?php } ?>
					<span class="command" data-map="1" onclick="f_map_set(event);">Map&nbsp;1</span>
					<?php for($i = 2; $i <= PB_MAPS_COUNT; $i++) { ?>
						<span class="command" data-map="<?php eh($i); ?>" onclick="f_map_set(event);"><?php eh($i); ?></span>
					<?php } ?>
					<?php if($row[14]) { ?>
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
		<?php if($uid) { ?>
		<form id="contacts" method="post" action="?action=export_selected">
			<input id="list" type="hidden" name="list" value="" />
		</form>
		<a href="#" onclick="f_export_selected(event); return false;">Export selected</a>
		<?php } ?>
		<br />
		<br />
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
		<form method="post" id="photo-upload" name="photo-upload">		
			<input id="upload" type="file" name="photo" style="display: none"/>
		</form>
<?php include("tpl.footer.php"); ?>