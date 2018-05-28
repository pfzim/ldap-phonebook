<?php include("tpl.header.php"); ?>
		<h3 align="center">LDAP Phonebook</h3>
		<div>
			<span><b>Brithdays:</b></span>
		<?php $i = 0; foreach($birthdays as &$row) { $i++; ?>
			<span><b><?php eh($row[15]); ?></b></span> <span><?php eh($row[2].' '.$row[3]); ?></span>
		<?php } ?>
		</div>
		<div id="imgblock" class="user-photo"><img id="userphoto" src=""/></div>
		<input type="text" id="search" class="form-field" onkeyup="filter_table()" placeholder="Search..">
		<span class="command" onclick="gi('search').value = ''; filter_table();">Reset</span>
		<?php if($uid) { ?>
		<span class="command f-right" onclick="f_edit(null, 'contact');">Add contact</span>
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
		<?php $i = 0; foreach($db->data as &$row) { $i++; ?>
			<tr id="<?php eh("row".$row[0]);?>" data-id=<?php eh($row[0]);?> data-map=<?php eh($row[11]); ?> data-x=<?php eh($row[12]); ?> data-y=<?php eh($row[13]); ?> data-photo=<?php eh($row[10]); ?>>
				<?php if($uid) { ?>
				<td><input type="checkbox" name="check" value="<?php eh($row[0]); ?>"/></td>
				<?php } ?>
				<td onclick="f_sw_map(event);" onmouseenter="f_sw_img(event);" onmouseleave="gi('imgblock').style.display = 'none'" onmousemove="f_mv_img(event);" style="cursor: pointer;" class="<?php if(intval($row[10])) { eh('userwithphoto'); } ?>"><?php eh($row[2].' '.$row[3]); ?></td>
				<td><?php eh($row[7]); ?></td>
				<td><?php eh($row[8]); ?></td>
				<td><a href="mailto:<?php eh($row[9]); ?>"><?php eh($row[9]); ?></a></td>
				<td><?php eh($row[6]); ?></td>
				<td><?php eh($row[4]); ?></td>
				<?php if($uid) { ?>
				<td>
					<span class="command" onclick="f_menu(event);">Menu</span>
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

<?php include("tpl.form-edit.php"); ?>
<?php include("tpl.form-upload.php"); ?>
<?php include("tpl.map-container.php"); ?>
<?php include("tpl.menu-contact.php"); ?>
<?php include("tpl.footer.php"); ?>
