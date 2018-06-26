<?php include("tpl.header.php"); ?>
		<h3 align="center">LDAP Phonebook</h3>
		<input type="text" id="search" class="form-field" onkeyup="filter_table()" placeholder="Search..">
		<span class="command" onclick="gi('search').value = ''; filter_table();">Reset</span>
		<table id="table" class="main-table">
			<thead>
			<tr>
				<th width="20%" onclick="sortTable(<?php eh($i++); ?>)">Date</th>
				<th width="20%" onclick="sortTable(<?php eh($i++); ?>)">User</th>
				<th width="20%" onclick="sortTable(<?php eh($i++); ?>)">Computer</th>
				<th width="20%" onclick="sortTable(<?php eh($i++); ?>)">IP</th>
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

<?php include("tpl.menu-contact.php"); ?>
<?php include("tpl.footer.php"); ?>
