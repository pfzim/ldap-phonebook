<?php include(ABSPATH.'templ'.DIRECTORY_SEPARATOR.'tpl.header.php'); ?>
		<h3 align="center"><?php eh($lang["mainLDAPPhonebook"]) ?></h3>
		<div>
			<span><b><?php eh($lang["mainBrithdays"]) ?></b></span>
			<?php 
				if($birthdays) {
					foreach($birthdays as &$row) {?>
						<br><span><b><?php eh($row["DayMonth"]); ?></b></span>&nbsp;<span><?php eh($row["lname"].' '.$row["fname"]); ?></span>
			<?php 
					} 
				} else { ?>
					<span><?php eh($lang["mainNoBirthdays"]) ?></span>
			<?php 
				}?>
		</div>
		<div id="imgblock" class="user-photo"><img id="userphoto" src=""/></div>
		<input type="text" id="search" class="form-field" onkeyup="filter_table()" placeholder=<?php eh($lang["mainSearch"]) ?>>
		<span class="command" onclick="gi('search').value = ''; filter_table();"><?php eh($lang["mainReset"]) ?></span>
		<?php if($uid) { ?>
		<span class="command f-right" onclick="f_edit(null, 'contact');"><?php eh($lang["mainAddСontact"]) ?></span>
		<?php } ?>
		<table id="table" class="main-table">
			<thead>
			<tr>
				<?php $i = 0; ?>
				<?php if($uid) { ?>
				<th width="1%"><input type="checkbox" onclick="f_select_all(event)"/></th>
				<?php $i++; } ?>
				<th width="20%" onclick="sortTable(<?php eh($i++); ?>)"><?php eh($lang["mainName"]) ?></th>
				<th width="10%" onclick="sortTable(<?php eh($i++); ?>)"><?php eh($lang["mainPhone"]) ?></th>
				<th width="10%" onclick="sortTable(<?php eh($i++); ?>)"><?php eh($lang["mainPhoneCity"]) ?></th>
				<th width="10%" onclick="sortTable(<?php eh($i++); ?>)"><?php eh($lang["mainMobile"]) ?></th>
				<th width="15%" onclick="sortTable(<?php eh($i++); ?>)"><?php eh($lang["mainEMail"]) ?></th>
				<th width="10%" onclick="sortTable(<?php eh($i++); ?>)"><?php eh($lang["mainPosition"]) ?></th>
				<th width="10%" onclick="sortTable(<?php eh($i++); ?>)"><?php eh($lang["mainDepartment"]) ?></th>
				<?php if($uid) { ?>
				<th width="15%"><?php eh($lang["mainOperations"]) ?></th>
				<?php } ?>
			</tr>
			</thead>
			<tbody id="table-data">
		<?php $i = 0; foreach($db->data as &$row) { $i++; ?>
			<tr id="<?php eh("row".$row["id"]);?>" data-id=<?php eh($row["id"]);?> data-map=<?php eh($row["map"]); ?> data-x=<?php eh($row["x"]); ?> data-y=<?php eh($row["y"]); ?> data-photo=<?php eh($row["photo"]); ?>>
				<?php if($uid) { ?>
				<td><input type="checkbox" name="check" value="<?php eh($row["id"]); ?>"/></td>
				<?php } ?>
				<td id="<?php eh("nameCell".$row["id"]);?>" onclick="f_sw_map(event);" onmouseenter="f_sw_img(event);" onmouseleave="gi('imgblock').style.display = 'none'" onmousemove="f_mv_img(event);" style="cursor: pointer;" class="<?php if(intval($row["photo"])) { eh('userwithphoto'); } ?>"><?php eh($row["lname"].' '.$row["fname"]); ?></td>
				<td id="<?php eh("pintCell".$row["id"]);?>"><?php eh($row["pint"]); ?></td>
				<td id="<?php eh("pcityCell".$row["id"]);?>"><?php eh($row["pcity"]); ?></td>
				<td id="<?php eh("pcellCell".$row["id"]);?>"><?php eh($row["pcell"]); ?></td>
				<td id="<?php eh("mailCell".$row["id"]);?>"><a href="mailto:<?php eh($row["mail"]); ?>"><?php eh($row["mail"]); ?></a></td>
				<td id="<?php eh("posCell".$row["id"]);?>"><?php eh($row["pos"]); ?></td>
				<td id="<?php eh("depCell".$row["id"]);?>"><?php eh($row["dep"]); ?></td>
				<?php if($uid) { ?>
				<td id="<?php eh("mainMenuCell".$row["id"]);?>">
					<span class="command" onclick="f_menu(event);"><?php eh($lang["mainMenu"]) ?></span>
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
		<a href="#" onclick="f_export_selected(event); return false;"><?php eh($lang["mainExportSelected"]) ?></a>
		<?php } ?>
		<br />
		<br />

<?php include(ABSPATH.'templ'.DIRECTORY_SEPARATOR.'tpl.form-edit.php'); ?>
<?php include(ABSPATH.'templ'.DIRECTORY_SEPARATOR.'tpl.form-upload.php'); ?>
<?php include(ABSPATH.'templ'.DIRECTORY_SEPARATOR.'tpl.map-container.php'); ?>
<?php include(ABSPATH.'templ'.DIRECTORY_SEPARATOR.'tpl.menu-contact.php'); ?>
<?php include(ABSPATH.'templ'.DIRECTORY_SEPARATOR.'tpl.footer.php'); ?>
