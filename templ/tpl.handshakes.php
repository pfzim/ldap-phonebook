<?php include(ABSPATH.'templ'.DIRECTORY_SEPARATOR.'tpl.header.php'); ?>
		<h3 align="center"><?php eh($lang["handshakesLDAPPhonebook"]) ?></h3>
		<input type="text" id="search" class="form-field" onkeyup="filter_table()" placeholder="<?php eh($lang["handshakesSearch"]) ?>">
		<span class="command" onclick="gi('search').value = ''; filter_table();"><?php eh($lang["handshakesReset"]) ?></span>
		<table id="table" class="main-table">
			<thead>
			<tr>
				<th width="20%" onclick="sortTable(<?php eh($i++); ?>)"><?php eh($lang["handshakesDate"]) ?></th>
				<th width="20%" onclick="sortTable(<?php eh($i++); ?>)"><?php eh($lang["handshakesUser"]) ?></th>
				<th width="20%" onclick="sortTable(<?php eh($i++); ?>)"><?php eh($lang["handshakesComputer"]) ?></th>
				<th width="20%" onclick="sortTable(<?php eh($i++); ?>)"><?php eh($lang["handshakesIP"]) ?></th>
			</tr>
			</thead>
			<tbody id="table-data">
		<?php $i = 0; foreach($handshakes as &$row) { $i++; ?>
			<tr id="<?php eh("row".$row[0]);?>" data-id=<?php eh($row[0]);?>>
				<td><?php eh($row[1]); ?></td>
				<td><?php eh($row[2]); ?></td>
				<td><a href="msraurl:<?php eh($row[3]); ?>"><?php eh($row[3]); ?></a></td>
				<td><?php eh($row[4]); ?></td>
			</tr>
		<?php } ?>
			</tbody>
		</table>
		<br />
		<br />

<?php include(ABSPATH.'templ'.DIRECTORY_SEPARATOR.'tpl.menu-contact.php'); ?>
<?php include(ABSPATH.'templ'.DIRECTORY_SEPARATOR.'tpl.footer.php'); ?>
